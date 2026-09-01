/* eslint-disable @typescript-eslint/no-explicit-any */
'use client';

import React, { useState, useEffect, forwardRef, useImperativeHandle } from 'react';
import { ThreatMetrixScript } from './ThreatMetrixScript';
import { CardinalDataCollector } from './CardinalDataCollector';
import { StepUpChallengeModal } from './StepUpChallengeModal';
import { atcService, AtcEnrollmentResponse } from '@/services/atcService';
import { useAuth } from '@/context/AuthContext';

export interface AtcPaymentContext {
  amount: number;
  currency: string;
  campaignId?: number;
  programId?: number;
  isRecurring: boolean;
  donorInfo?: {
    firstName?: string;
    lastName?: string;
    email?: string;
    phone?: string;
  };
}

export interface AtcCreditCardFormRef {
  startPayment: (ctx: AtcPaymentContext) => void;
  validateForm: () => boolean;
}

interface AtcCreditCardFormProps {
  onStatusChange?: (status: string, message: string) => void;
  onSuccess?: (details: any) => void;
  onError?: (error: any) => void;
}

export const AtcCreditCardForm = forwardRef<AtcCreditCardFormRef, AtcCreditCardFormProps>(({
  onStatusChange,
  onSuccess,
  onError,
}, ref) => {
  // Form State
  const [cardNumber, setCardNumber] = useState('');
  const [expirationMonth, setExpirationMonth] = useState('12');
  const [expirationYear, setExpirationYear] = useState('2028');
  const [cvv, setCvv] = useState('');
  const [cardholderName, setCardholderName] = useState('');
  const [email, setEmail] = useState('');

  // Location / AVS State
  const [isInternational, setIsInternational] = useState(false);
  const [department, setDepartment] = useState('L'); // Default: La Paz (L)
  const [locality, setLocality] = useState('');
  const [stateProvince, setStateProvince] = useState('');
  const [country, setCountry] = useState('US'); // Default International: US
  const [address1, setAddress1] = useState('');
  const [postalCode, setPostalCode] = useState('');
  
  // Stored Payment Context from Parent
  const [paymentCtx, setPaymentCtx] = useState<AtcPaymentContext | null>(null);
  const { user } = useAuth();

  // Pre-fill cardholderName & email if user is logged in
  useEffect(() => {
    if (user) {
      if (user.name && !cardholderName) setCardholderName(user.name.toUpperCase());
      if (user.email && !email) setEmail(user.email);
    }
  }, [user]);

  // Workflow State
  const [step, setStep] = useState<'IDLE' | 'INITIATING' | 'COLLECTING' | 'AUTHENTICATING' | 'CHALLENGE' | 'CAPTURING' | 'SUCCESS' | 'ERROR'>('IDLE');
  const [statusMessage, setStatusMessage] = useState('');
  const [errorMessage, setErrorMessage] = useState('');

  const updateStatus = (newStep: typeof step, msg: string) => {
    setStep(newStep);
    setStatusMessage(msg);
    if (onStatusChange) onStatusChange(newStep, msg);
  };
  
  const updateError = (msg: string, errObj?: any) => {
    setStep('ERROR');
    setErrorMessage(msg);
    if (onStatusChange) onStatusChange('ERROR', msg);
    if (onError) onError(errObj || new Error(msg));
  };

  // 3DS Session State
  const [fingerprintSessionId, setFingerprintSessionId] = useState('');
  const [setupData, setSetupData] = useState<{ referenceId: string; accessToken: string; merchantReferenceNumber: string } | null>(null);
  const [enrollmentResult, setEnrollmentResult] = useState<AtcEnrollmentResponse | null>(null);
  const [isStepUpOpen, setIsStepUpOpen] = useState(false);

  // Listen for Step-Up return callback window message from iframe
  useEffect(() => {
    const handleMessage = (event: MessageEvent) => {
      if (event.data && (event.data.type === 'STEP_UP_COMPLETED' || event.data === 'STEP_UP_COMPLETED')) {
        console.log('[ATC 3DS2]: Received StepUp completed message from iframe callback');
        handleStepUpCompleted();
      }
    };
    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, [enrollmentResult, setupData, paymentCtx]);

  // Auto-Detect Card Brand (VISA, MASTERCARD, AMEX)
  const getCardType = (num: string) => {
    const clean = num.replace(/\s+/g, '');
    if (/^4/.test(clean)) return 'VISA';
    if (/^5[1-5]|^2[2-7]/.test(clean)) return 'MASTERCARD';
    if (/^3[47]/.test(clean)) return 'AMEX';
    return 'VISA';
  };

  useImperativeHandle(ref, () => ({
    validateForm: () => {
      if (!cardNumber || !expirationMonth || !expirationYear || !cvv || !cardholderName || !email) {
        setErrorMessage('Por favor completa todos los datos de la tarjeta (incluyendo nombre y correo).');
        return false;
      }
      setErrorMessage('');
      return true;
    },
    startPayment: async (ctx: AtcPaymentContext) => {
      setPaymentCtx(ctx);
      setErrorMessage('');
      updateStatus('INITIATING', 'Iniciando sesión segura con Cybersource (3DS2)...');

      try {
        // 1. Paso 1: Setup Service Backend
        const cleanCard = cardNumber.replace(/\s+/g, '');
        const setupRes = await atcService.setupAuthentication({
          card_number: cleanCard,
          expiration_month: expirationMonth,
          expiration_year: expirationYear,
        });

        if (!setupRes.success || !setupRes.referenceId || !setupRes.accessToken) {
          throw new Error(setupRes.message || 'No se pudo generar el token de sesión 3DS2.');
        }

        setSetupData({
          referenceId: setupRes.referenceId,
          accessToken: setupRes.accessToken,
          merchantReferenceNumber: setupRes.merchantReferenceNumber,
        });

        updateStatus('COLLECTING', 'Recolectando perfil de riesgo de dispositivo (Cardinal Cruise)...');
      } catch (err: any) {
        console.error('[ATC Form Error]:', err);
        updateError(err.message || 'Ocurrió un error inesperado al iniciar la donación.', err);
      }
    }
  }));

  // Called when Cardinal Cruise Data Collection finishes
  const handleDataCollectionComplete = async () => {
    if (!setupData || step !== 'COLLECTING' || !paymentCtx) return;

    updateStatus('AUTHENTICATING', 'Evaluando riesgo bancario (Check Enrollment)...');

    try {
      const cleanCard = cardNumber.replace(/\s+/g, '');

      const activeFingerprintId = fingerprintSessionId || `redenlace_000021_${Date.now()}`;
      if (!fingerprintSessionId) {
        setFingerprintSessionId(activeFingerprintId);
      }

      // 2. Paso 3: Check Enrollment Backend
      const enrollRes = await atcService.checkEnrollment({
        referenceId: setupData.referenceId,
        fingerprintSessionId: activeFingerprintId,
        merchantReferenceNumber: setupData.merchantReferenceNumber,
        amount: paymentCtx.amount,
        currency: paymentCtx.currency,
        card_number: cleanCard,
        expiration_month: expirationMonth,
        expiration_year: expirationYear,
        cvv: cvv,
        first_name: cardholderName ? cardholderName.trim().split(' ')[0] : (paymentCtx.donorInfo?.firstName || 'Donante'),
        last_name: cardholderName ? (cardholderName.trim().split(' ').slice(1).join(' ') || 'Donante') : (paymentCtx.donorInfo?.lastName || 'ATC'),
        email: email ? email.trim() : (paymentCtx.donorInfo?.email || 'donante@fundacion.org'),
        state: isInternational ? (stateProvince || 'FL') : department,
        locality: isInternational ? (locality || 'Miami') : 'La Paz',
        country: isInternational ? (country || 'US') : 'BO',
        address1: address1.trim() || (isInternational ? '100 Biscayne Blvd' : 'Av. Principal 123'),
        postal_code: isInternational ? (postalCode || '33101') : (postalCode || '0000'),
      });

      setEnrollmentResult(enrollRes);

      if (!enrollRes.success) {
        const errorDetail = enrollRes.message || (enrollRes as any).error?.message || 'La tarjeta no pudo ser autenticada por el banco.';
        updateError(errorDetail);
        return;
      }

      if (enrollRes.isChallengeRequired && enrollRes.stepUpJwt) {
        // Flujo Challenge (Step-Up Required)
        updateStatus('CHALLENGE', 'Desafío bancario requerido (OTP/SMS)...');
        setIsStepUpOpen(true);
      } else {
        // Flujo Frictionless (Sin Fricción) -> Proceder directo al cobro
        await executeFinalPayment(enrollRes);
      }
    } catch (err: any) {
      console.error('[ATC Enrollment Error]:', err);
      updateError(err.message || 'Error al autenticar la tarjeta con el banco.', err);
    }
  };

  const handleStepUpCompleted = async () => {
    setIsStepUpOpen(false);
    if (!enrollmentResult || !enrollmentResult.authenticationTransactionId) {
      await executeFinalPayment(enrollmentResult || {});
      return;
    }

    updateStatus('AUTHENTICATING', 'Validando confirmación de desafío con el banco emisor...');

    try {
      // 3. Paso 5: Validate Challenge Backend
      const valRes = await atcService.validateChallenge({
        authenticationTransactionId: enrollmentResult.authenticationTransactionId,
        merchantReferenceNumber: setupData?.merchantReferenceNumber,
      });

      if (!valRes.success) {
        throw new Error('La autenticación 3DS2 falló o fue rechazada por el banco.');
      }

      await executeFinalPayment({
        ...enrollmentResult,
        eci: valRes.eci || enrollmentResult.eci,
        cavv: valRes.cavv || enrollmentResult.cavv,
      });
    } catch (err: any) {
      updateError(err.message || 'Error al validar el desafío bancario.', err);
    }
  };

  // 4. Paso 6: Payment Capture & Tokenization Backend
  const executeFinalPayment = async (authDetails: Partial<AtcEnrollmentResponse>) => {
    if (!paymentCtx) return;
    updateStatus('CAPTURING', 'Procesando cobro financiero y registrando donación...');

    try {
      const cleanCard = cardNumber.replace(/\s+/g, '');
      const payRes = await atcService.processPayment({
        merchantReferenceNumber: setupData?.merchantReferenceNumber || `ATC-REF-${Date.now()}`,
        amount: paymentCtx.amount,
        currency: paymentCtx.currency,
        card_number: cleanCard,
        expiration_month: expirationMonth,
        expiration_year: expirationYear,
        cvv: cvv,
        card_type: getCardType(cleanCard),
        first_name: cardholderName ? cardholderName.trim().split(' ')[0] : (paymentCtx.donorInfo?.firstName || 'Donante'),
        last_name: cardholderName ? (cardholderName.trim().split(' ').slice(1).join(' ') || 'Donante') : (paymentCtx.donorInfo?.lastName || 'ATC'),
        email: email ? email.trim() : (paymentCtx.donorInfo?.email || 'donante@fundacion.org'),
        state: isInternational ? (stateProvince || 'FL') : department,
        locality: isInternational ? (locality || 'Miami') : 'La Paz',
        country: isInternational ? (country || 'US') : 'BO',
        address1: address1.trim() || (isInternational ? '100 Biscayne Blvd' : 'Av. Principal 123'),
        postal_code: isInternational ? (postalCode || '33101') : (postalCode || '0000'),
        fingerprintSessionId: fingerprintSessionId,
        eci: authDetails.eci,
        cavv: authDetails.cavv,
        ucafAuthenticationData: authDetails.ucafAuthenticationData,
        ucafCollectionIndicator: authDetails.ucafCollectionIndicator,
        xid: authDetails.xid,
        threeDSServerTransactionId: authDetails.threeDSServerTransactionId,
        specificationVersion: authDetails.specificationVersion,
        is_recurring: paymentCtx.isRecurring,
        campaign_id: paymentCtx.campaignId,
        program_id: paymentCtx.programId,
      });

      if (!payRes.success) {
        throw new Error(payRes.message || 'El cobro con tarjeta fue rechazado.');
      }

      updateStatus('SUCCESS', '¡Donación procesada con éxito!');
      if (onSuccess) onSuccess(payRes);
    } catch (err: any) {
      updateError(err.message || 'El cobro no pudo ser procesado.', err);
    }
  };

  return (
    <div className="w-full">
      {/* 1. Componente ThreatMetrix (Device Profiling) */}
      <ThreatMetrixScript
        onSessionGenerated={(sid) => setFingerprintSessionId(sid)}
      />

      {/* 2. Componente Cardinal Cruise (Data Collection Iframe) */}
      {setupData?.accessToken && (
        <CardinalDataCollector
          jwt={setupData.accessToken}
          onComplete={handleDataCollectionComplete}
        />
      )}

      {step === 'SUCCESS' ? (
        <div className="text-center py-4 text-emerald-600 font-bold text-sm">
          ✅ Pago procesado exitosamente
        </div>
      ) : (
        <div className="space-y-4">
          {/* Removed internal frequency selector - handled by parent */}

          {/* Selector de Origen de Tarjeta: Bolivia vs Internacional */}
          <div className="bg-gray-50 p-3 rounded-xl border border-gray-200 mb-3">
            <div className="flex items-center justify-between mb-2">
              <label className="text-xs font-bold text-gray-700">Origen de la Tarjeta</label>
              <div className="flex space-x-1 text-xs">
                <button
                  type="button"
                  onClick={() => { setIsInternational(false); setDepartment('L'); }}
                  className={`px-2 py-1 rounded-md font-medium transition ${!isInternational ? 'bg-pink-600 text-white shadow-sm' : 'bg-gray-200 text-gray-700'}`}
                >
                  🇧🇴 Bolivia
                </button>
                <button
                  type="button"
                  onClick={() => setIsInternational(true)}
                  className={`px-2 py-1 rounded-md font-medium transition ${isInternational ? 'bg-pink-600 text-white shadow-sm' : 'bg-gray-200 text-gray-700'}`}
                >
                  🌐 Internacional
                </button>
              </div>
            </div>

            {!isInternational ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1">Departamento</label>
                  <select
                    value={department}
                    onChange={(e) => setDepartment(e.target.value)}
                    className="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-pink-500 outline-none"
                  >
                    <option value="L">La Paz</option>
                    <option value="S">Santa Cruz</option>
                    <option value="C">Cochabamba</option>
                    <option value="H">Chuquisaca (Sucre)</option>
                    <option value="T">Tarija</option>
                    <option value="O">Oruro</option>
                    <option value="P">Potosí</option>
                    <option value="B">Beni (Trinidad)</option>
                    <option value="N">Pando (Cobija)</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1">Dirección (Opcional)</label>
                  <input
                    type="text"
                    placeholder="Calle, avenida o zona"
                    value={address1}
                    onChange={(e) => setAddress1(e.target.value)}
                    className="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs outline-none bg-white"
                  />
                </div>
              </div>
            ) : (
              <div className="space-y-2 mt-2">
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-[11px] font-medium text-gray-600 mb-1">País</label>
                    <select
                      value={country}
                      onChange={(e) => setCountry(e.target.value)}
                      className="w-full px-2 py-1.5 border border-gray-300 rounded text-xs bg-white outline-none"
                    >
                      <option value="US">Estados Unidos</option>
                      <option value="ES">España</option>
                      <option value="AR">Argentina</option>
                      <option value="CL">Chile</option>
                      <option value="CO">Colombia</option>
                      <option value="PE">Perú</option>
                      <option value="MX">México</option>
                      <option value="BR">Brasil</option>
                      <option value="CA">Canadá</option>
                      <option value="DE">Alemania</option>
                      <option value="FR">Francia</option>
                      <option value="GB">Reino Unido</option>
                      <option value="IT">Italia</option>
                      <option value="UY">Uruguay</option>
                      <option value="PY">Paraguay</option>
                      <option value="EC">Ecuador</option>
                      <option value="VE">Venezuela</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-[11px] font-medium text-gray-600 mb-1">Estado / Provincia</label>
                    {country === 'US' ? (
                      <select
                        value={stateProvince || 'FL'}
                        onChange={(e) => setStateProvince(e.target.value)}
                        className="w-full px-2 py-1.5 border border-gray-300 rounded text-xs bg-white outline-none"
                      >
                        <option value="FL">Florida (FL)</option>
                        <option value="CA">California (CA)</option>
                        <option value="NY">New York (NY)</option>
                        <option value="TX">Texas (TX)</option>
                        <option value="IL">Illinois (IL)</option>
                        <option value="WA">Washington (WA)</option>
                        <option value="MA">Massachusetts (MA)</option>
                        <option value="NJ">New Jersey (NJ)</option>
                        <option value="PA">Pennsylvania (PA)</option>
                        <option value="GA">Georgia (GA)</option>
                        <option value="NC">North Carolina (NC)</option>
                        <option value="VA">Virginia (VA)</option>
                        <option value="OH">Ohio (OH)</option>
                        <option value="MI">Michigan (MI)</option>
                        <option value="AZ">Arizona (AZ)</option>
                        <option value="CO">Colorado (CO)</option>
                        <option value="MD">Maryland (MD)</option>
                        <option value="NV">Nevada (NV)</option>
                        <option value="OR">Oregon (OR)</option>
                        <option value="UT">Utah (UT)</option>
                      </select>
                    ) : (
                      <input
                        type="text"
                        placeholder="Estado o Provincia"
                        value={stateProvince}
                        onChange={(e) => setStateProvince(e.target.value)}
                        className="w-full px-2 py-1.5 border border-gray-300 rounded text-xs outline-none"
                      />
                    )}
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-[11px] font-medium text-gray-600 mb-1">Ciudad / Localidad</label>
                    <input
                      type="text"
                      placeholder="Miami, Madrid, etc."
                      value={locality}
                      onChange={(e) => setLocality(e.target.value)}
                      className="w-full px-2 py-1.5 border border-gray-300 rounded text-xs outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] font-medium text-gray-600 mb-1">Código Postal / Zip</label>
                    <input
                      type="text"
                      placeholder="90210, 28001"
                      value={postalCode}
                      onChange={(e) => setPostalCode(e.target.value)}
                      className="w-full px-2 py-1.5 border border-gray-300 rounded text-xs outline-none"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-[11px] font-medium text-gray-600 mb-1">Dirección de Facturación (Opcional)</label>
                  <input
                    type="text"
                    placeholder="Calle, número, depto."
                    value={address1}
                    onChange={(e) => setAddress1(e.target.value)}
                    className="w-full px-2 py-1.5 border border-gray-300 rounded text-xs outline-none"
                  />
                </div>
              </div>
            )}
          </div>

          {/* Nombre en la tarjeta */}
          <div>
            <label className="block text-xs font-medium text-gray-700 mb-1">Nombre Completo del Titular</label>
            <input
              type="text"
              name="ccname"
              autoComplete="cc-name"
              required
              placeholder="JUAN PEREZ"
              value={cardholderName}
              onChange={(e) => setCardholderName(e.target.value.toUpperCase())}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            />
          </div>

          {/* Correo para Certificado */}
          <div>
            <label className="block text-xs font-bold text-gray-700 mb-1">Correo Electrónico (Requerido por el banco)</label>
            <input
              type="email"
              name="email"
              autoComplete="email"
              required
              placeholder="juan@ejemplo.com"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            />
          </div>

          {/* Número de Tarjeta */}
          <div>
            <label className="block text-xs font-medium text-gray-700 mb-1">Número de Tarjeta</label>
            <input
              type="text"
              name="cardnumber"
              autoComplete="cc-number"
              required
              maxLength={19}
              placeholder="4000 0000 0000 1000"
              value={cardNumber}
              onChange={(e) => setCardNumber(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            />
          </div>

          {/* Mes, Año y CVV */}
          <div className="grid grid-cols-3 gap-3">
            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Mes Venc.</label>
              <select
                name="ccexpmonth"
                autoComplete="cc-exp-month"
                value={expirationMonth}
                onChange={(e) => setExpirationMonth(e.target.value)}
                className="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"
              >
                {Array.from({ length: 12 }, (_, i) => {
                  const m = (i + 1).toString().padStart(2, '0');
                  return <option key={m} value={m}>{m}</option>;
                })}
              </select>
            </div>

            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">Año Venc.</label>
              <select
                name="ccexpyear"
                autoComplete="cc-exp-year"
                value={expirationYear}
                onChange={(e) => setExpirationYear(e.target.value)}
                className="w-full px-2 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"
              >
                {Array.from({ length: 10 }, (_, i) => {
                  const y = (2026 + i).toString();
                  return <option key={y} value={y}>{y}</option>;
                })}
              </select>
            </div>

            <div>
              <label className="block text-xs font-medium text-gray-700 mb-1">CVV / CVC</label>
              <input
                type="password"
                name="cvc"
                autoComplete="cc-csc"
                required
                maxLength={4}
                placeholder="123"
                value={cvv}
                onChange={(e) => setCvv(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-center"
              />
            </div>
          </div>

          {/* Mensajes de Error */}
          {errorMessage && (
            <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl flex items-start space-x-2">
              <span className="font-bold">⚠️</span>
              <span>{errorMessage}</span>
            </div>
          )}

          {/* Estado de Carga / Eliminado botón interno porque el padre lo controla */}
        </div>
      )}

      {/* 0. Componente de Perfilado de Huella Digital de Dispositivo (Cybersource Decision Manager / ThreatMetrix) */}
      <ThreatMetrixScript
        merchantId="redenlace_000021"
        onSessionGenerated={(sid) => {
          console.log('[ThreatMetrix DFP]: Device Fingerprint Session ID generated:', sid);
          setFingerprintSessionId(sid);
        }}
      />

      {/* 3. Componente Modal de Desafío 3DS (Step-Up OTP) */}
      <StepUpChallengeModal
        isOpen={isStepUpOpen}
        stepUpJwt={enrollmentResult?.stepUpJwt || ''}
        stepUpUrl={enrollmentResult?.stepUpUrl}
        onSuccess={handleStepUpCompleted}
        onCancel={() => {
          setIsStepUpOpen(false);
          updateError('La verificación de seguridad 3DS fue cancelada.');
        }}
      />
    </div>
  );
});

AtcCreditCardForm.displayName = 'AtcCreditCardForm';

