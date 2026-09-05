"use client";
import React, { useState, useEffect, useRef, Suspense } from 'react';
import { donationService, DonationTier, QrResponse } from '@/services/donationService';
import { useAuth } from '@/context/AuthContext';
import { useRouter, useSearchParams } from 'next/navigation';
import NextImage from 'next/image';
import { AtcCreditCardForm, AtcCreditCardFormRef, AtcPaymentContext } from './donations/atc/AtcCreditCardForm';

interface DonationFormProps {
    onClose?: () => void;
    isInModal?: boolean;
    campaignIdProp?: number;
}

interface ReactivationData {
    amount?: number | string;
    currency?: string;
    donor_name?: string;
    donor_email?: string;
    campaign_id?: number;
    campaign_name?: string;
    has_saved_card?: boolean;
}

interface CampaignData {
    id: number;
    title?: string;
    allowed_frequencies?: string;
    allowed_payment_methods?: string;
    allowed_currencies?: string;
    [key: string]: unknown;
}

const PRESET_TIERS: Record<'Bs' | 'USD', Array<{ amount: string; label: string }>> = {
    Bs: [
        { amount: '50', label: '50 Bs' },
        { amount: '100', label: '100 Bs' },
        { amount: '200', label: '200 Bs' },
        { amount: '500', label: '500 Bs' },
    ],
    USD: [
        { amount: '10', label: '$10 USD' },
        { amount: '25', label: '$25 USD' },
        { amount: '50', label: '$50 USD' },
        { amount: '100', label: '$100 USD' },
    ],
};

const DonationFormContent: React.FC<DonationFormProps> = ({ onClose, isInModal = false, campaignIdProp }) => {
    const { user } = useAuth();
    const router = useRouter();
    const searchParams = useSearchParams();
    const campaignIdParam = searchParams.get('campaign_id');
    const campaignId = campaignIdProp !== undefined ? campaignIdProp : (campaignIdParam ? parseInt(campaignIdParam) : undefined);

    // Wizard Steps
    const [step, setStep] = useState<1 | 2 | 3>(1);

    // Data States
    const [selectedPreset, setSelectedPreset] = useState<string>('100');
    const [customAmount, setCustomAmount] = useState<string>('');
    const [currency, setCurrency] = useState<'Bs' | 'USD'>('Bs');
    const [frequency, setFrequency] = useState<'once' | 'monthly'>('monthly');
    const [paymentMethod, setPaymentMethod] = useState<'card' | 'qr'>('card');
    const [isAnonymous, setIsAnonymous] = useState(false);
    
    // Donor Details
    const [donorName, setDonorName] = useState('');
    const [donorEmail, setDonorEmail] = useState('');
    const [donorCi, setDonorCi] = useState('');
    const [donorPhone, setDonorPhone] = useState('');
    
    // QR & ATC Status
    const [loading, setLoading] = useState(false);
    const [qrData, setQrData] = useState<QrResponse | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [status, setStatus] = useState<string | null>(null);
    const [simulating, setSimulating] = useState(false);
    const [atcStatusMsg, setAtcStatusMsg] = useState('');
    
    // Contextual Campaign Data
    const [campaign, setCampaign] = useState<CampaignData | null>(null);
    const [hasDraft, setHasDraft] = useState(false);
    const [reactivationData, setReactivationData] = useState<ReactivationData | null>(null);
    
    const cardFormRef = useRef<AtcCreditCardFormRef>(null);

    const reactivateToken = searchParams.get('reactivate_token') || searchParams.get('token');

    // Reactivation Token Validation
    useEffect(() => {
        if (!reactivateToken) return;

        const validateToken = async () => {
            try {
                const res = await fetch(`http://127.0.0.1:8000/api/v1/subscriptions/validate-reactivation/${reactivateToken}`);
                const data = await res.json();
                if (res.ok && data.success && data.data) {
                    setReactivationData(data.data);
                    setFrequency('monthly');
                    setPaymentMethod('card');
                    if (data.data.amount) {
                        setCustomAmount(data.data.amount.toString());
                        setSelectedPreset('');
                    }
                    if (data.data.currency) {
                        setCurrency(data.data.currency === 'USD' ? 'USD' : 'Bs');
                    }
                    if (data.data.donor_name) setDonorName(data.data.donor_name);
                    if (data.data.donor_email) setDonorEmail(data.data.donor_email);
                    if (data.data.campaign_id) {
                        const campaigns = await donationService.getCampaigns();
                        const currentCampaign = campaigns.find((c: { id: number }) => c.id === data.data.campaign_id);
                        if (currentCampaign) setCampaign(currentCampaign as unknown as CampaignData);
                    }
                } else {
                    setError(data.message || "El enlace de reactivación es inválido o ha expirado.");
                }
            } catch (err) {
                console.error("Error validando token de reactivación", err);
            }
        };

        validateToken();
    }, [reactivateToken]);

    const handleOneClickReactivate = async () => {
        if (!reactivateToken) return;
        setLoading(true);
        setError(null);
        try {
            const res = await fetch(`http://127.0.0.1:8000/api/v1/subscriptions/confirm-reactivation/${reactivateToken}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
            });
            const data = await res.json();
            if (res.ok && data.success) {
                setStatus('paid');
            } else {
                setError(data.message || "No se pudo reactivar la suscripción.");
            }
        } catch (err) {
            setError("Error de conexión al reactivar la suscripción.");
        } finally {
            setLoading(false);
        }
    };

    // Initial Options & Campaign
    useEffect(() => {
        const fetchOptionsAndCampaign = async () => {
            try {
                if (campaignId) {
                    const campaigns = await donationService.getCampaigns();
                    const currentCampaign = campaigns.find((c: { id: number }) => c.id === campaignId);
                    if (currentCampaign) {
                        setCampaign(currentCampaign as unknown as CampaignData);
                        
                        // Enforce default rules
                        const campObj = currentCampaign as { allowed_frequencies?: string; allowed_payment_methods?: string; allowed_currencies?: string };
                        if (campObj.allowed_frequencies === 'monthly_only') {
                            setFrequency('monthly');
                            setPaymentMethod('card');
                        } else if (campObj.allowed_frequencies === 'once_only') {
                            setFrequency('once');
                        }
                        
                        if (campObj.allowed_payment_methods === 'qr_only' && campObj.allowed_frequencies !== 'monthly_only') {
                            setPaymentMethod('qr');
                            setFrequency('once');
                            setCurrency('Bs');
                        } else if (campObj.allowed_payment_methods === 'card_only') {
                            setPaymentMethod('card');
                        }

                        if (campObj.allowed_currencies === 'bob_only') {
                            setCurrency('Bs');
                        } else if (campObj.allowed_currencies === 'usd_only') {
                            setCurrency('USD');
                            setPaymentMethod('card');
                        }
                    }
                }

                // Check Draft
                const draftStr = localStorage.getItem('donation_draft');
                if (draftStr) {
                    setHasDraft(true);
                }

            } catch (err) {
                console.error("Failed to fetch campaign details", err);
            }
        };
        fetchOptionsAndCampaign();
    }, [campaignId]);

    // Enforce QR -> Once only and BOB only
    useEffect(() => {
        if (paymentMethod === 'qr') {
            setFrequency('once');
            setCurrency('Bs');
        }
    }, [paymentMethod]);

    // Pre-fill user data (only if NOT in reactivation flow)
    useEffect(() => {
        if (user && !reactivationData) {
            if (user.name) setDonorName(user.name);
            if (user.email) setDonorEmail(user.email);
            setIsAnonymous(false);
        }
    }, [user, reactivationData]);

    // Polling for QR status
    useEffect(() => {
        let interval: NodeJS.Timeout;
        const POLLING_TIMEOUT = 15 * 60 * 1000;
        const startTime = Date.now();

        if (step === 3 && paymentMethod === 'qr' && qrData && qrData.qr_id && status !== 'paid') {
            interval = setInterval(async () => {
                if (Date.now() - startTime > POLLING_TIMEOUT) {
                    clearInterval(interval);
                    setError("El tiempo de espera ha expirado. Por favor genera un nuevo QR.");
                    return;
                }
                try {
                    let data;
                    if (frequency === 'monthly') {
                        data = await donationService.checkSubscriptionStatus(qrData.qr_id);
                    } else {
                        data = await donationService.checkStatus(qrData.qr_id);
                    }
                    
                    if (data.status === 'paid' || data.status === '2') {
                        setStatus('paid');
                        clearInterval(interval);
                    }
                } catch (err) {
                    console.error("Polling error", err);
                }
            }, 5000);
        }
        return () => clearInterval(interval);
    }, [step, paymentMethod, qrData, status, frequency]);

    const finalAmountStr = customAmount || selectedPreset;
    const finalAmount = parseFloat(finalAmountStr || '0');
    const currencyStr = currency;

    // --- Handlers ---
    const handleNextToStep2 = async () => {
        setError(null);
        if (finalAmount <= 0) {
            setError("Por favor selecciona un monto o introduce uno válido.");
            return;
        }
        
        if (paymentMethod === 'card') {
            if (!cardFormRef.current?.validateForm()) {
                return; // Error is handled inside AtcCreditCardForm
            }
        }
        
        // Save Draft
        localStorage.setItem('donation_draft', JSON.stringify({
            amount: finalAmount,
            currency: currencyStr,
            frequency,
            paymentMethod,
            campaignId
        }));
        
        // CRO Optimization: If user is logged in OR using card, bypass Step 2 and finalize immediately!
        if (user || isAnonymous || paymentMethod === 'card') {
            const effectiveName = user ? user.name : (donorName || 'Donante');
            const effectiveEmail = user ? user.email : (donorEmail || 'donante@fundacion.org');
            await executePayment(effectiveName, effectiveEmail);
        } else {
            setStep(2);
        }
    };

    const executePayment = async (name: string, email: string) => {
        if (paymentMethod === 'qr') {
            await generateQr(name, email);
        } else {
            // ATC Credit Card
            setLoading(true);
            const ctx: AtcPaymentContext = {
                amount: finalAmount,
                currency: currencyStr === 'Bs' ? 'BOB' : 'USD',
                isRecurring: frequency === 'monthly',
                campaignId: campaignId,
                donorInfo: isAnonymous ? undefined : {
                    firstName: name.split(' ')[0],
                    lastName: name.split(' ').slice(1).join(' '),
                    email: email,
                    phone: donorPhone
                }
            };
            cardFormRef.current?.startPayment(ctx);
            setStep(3); // Move to confirm step to show loading/3DS
        }
    };

    const handleFinalize = async () => {
        setError(null);
        if (!isAnonymous && !user) {
            if (!donorName.trim() || !donorEmail.trim()) {
                setError("Por favor ingresa tu nombre y correo electrónico.");
                return;
            }
        }
        await executePayment(user ? user.name : donorName, user ? user.email : donorEmail);
    };

    const generateQr = async (paramName?: string, paramEmail?: string) => {
        setLoading(true);
        setError(null);
        setQrData(null);
        
        try {
            let res: QrResponse;
            const effectiveName = paramName || donorName || (user ? user.name : 'Donante');
            const effectiveEmail = paramEmail || donorEmail || (user ? user.email : '');
            const details = isAnonymous ? undefined : { name: effectiveName, ci: donorCi, phone: donorPhone, email: effectiveEmail };
            
            if (frequency === 'monthly') {
                if (!details || !details.email) throw new Error("Faltan datos requeridos para donación mensual");
                res = await donationService.requestSubscriptionQr(finalAmount, details, campaignId);
            } else {
                res = await donationService.requestQr(undefined, finalAmount, isAnonymous, details, campaignId);
            }
            
            setQrData(res);
            setStep(3);

        } catch (err) {
            const message = err instanceof Error ? err.message : "Ocurrió un error inesperado al generar el QR.";
            setError(message);
            setStep(2);
        } finally {
            setLoading(false);
        }
    };

    const handleSimulatePayment = async () => {
        if (!qrData || simulating) return;
        setSimulating(true);
        try {
            await donationService.simulatePayment(qrData.qr_id, isAnonymous ? "Donante Anónimo" : donorName);
        } catch (err) {
            console.error("Simulation failed", err);
            setError("Error simulando el pago.");
        } finally {
            setSimulating(false);
        }
    };

    const resetFlow = () => {
        setStep(1);
        setStatus(null);
        setQrData(null);
        setLoading(false);
        setReactivationData(null);
        if (typeof window !== 'undefined') {
            window.location.href = '/como-ayudar';
        }
    };

    const handleRestoreDraft = () => {
        const draftStr = localStorage.getItem('donation_draft');
        if (draftStr) {
            try {
                const draft = JSON.parse(draftStr);
                setCustomAmount(draft.amount.toString());
                setCurrency(draft.currency === 'USD' ? 'USD' : 'Bs');
                setSelectedPreset('');
                setFrequency(draft.frequency);
                setPaymentMethod(draft.paymentMethod);
                setStep(2);
            } catch (e) {
                console.error("Error parsing draft", e);
            }
        }
    };


    // --- Renders ---
    return (
        <div className={`bg-white rounded-2xl w-full ${isInModal ? '' : 'shadow-2xl max-w-5xl mx-auto my-8 overflow-hidden'}`}>
            
            {/* Header / Wizard */}
            <div className="bg-gray-50 border-b border-gray-100 p-6 flex items-center justify-between relative">
                {isInModal && onClose && (
                    <button onClick={onClose} className="absolute top-4 right-4 text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
                )}
                <div className="flex items-center gap-3">
                    <div className="text-rosa-principal">
                        <svg className="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    </div>
                    <div>
                        <h1 className="font-bold text-gray-900 text-lg leading-tight">Fundación</h1>
                        <h2 className="font-bold text-gray-900 text-lg leading-tight">Nuestra Esperanza</h2>
                    </div>
                </div>

                <div className="hidden md:flex items-center gap-4 text-sm font-semibold">
                    <div className={`flex items-center gap-2 ${step >= 1 ? 'text-rosa-principal' : 'text-gray-400'}`}>
                        <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs text-white ${step >= 1 ? 'bg-rosa-principal' : 'bg-gray-300'}`}>1</span>
                        Donación
                    </div>
                    <div className="w-8 h-px bg-gray-300"></div>
                    <div className={`flex items-center gap-2 ${step >= 2 ? 'text-rosa-principal' : 'text-gray-400'}`}>
                        <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs text-white ${step >= 2 ? 'bg-rosa-principal' : 'bg-gray-300'}`}>2</span>
                        Tus datos
                    </div>
                    <div className="w-8 h-px bg-gray-300"></div>
                    <div className={`flex items-center gap-2 ${step >= 3 ? 'text-rosa-principal' : 'text-gray-400'}`}>
                        <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs text-white ${step >= 3 ? 'bg-rosa-principal' : 'bg-gray-300'}`}>3</span>
                        Confirmación
                    </div>
                </div>

                <div className="hidden md:flex items-center gap-2 text-gray-500 text-sm">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Pago seguro
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-12 min-h-[600px]">
                
                {/* LEFT COLUMN: INTERACTIVE FORM */}
                <div className="md:col-span-7 p-6 md:p-10">
                    
                    {/* ESTADO ÉXITO */}
                    {status === 'paid' && (
                        <div className="text-center py-8 animate-fade-in">
                            <div className="w-20 h-20 bg-rosa-claro text-rosa-principal rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                                <svg className="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <h2 className="text-3xl font-bold text-gray-900 mb-3">¡Muchas gracias!</h2>
                            <p className="text-gray-600 mb-8 max-w-md mx-auto text-base">
                                {reactivationData 
                                    ? `Tu membresía recurrente de ${reactivationData.amount} ${reactivationData.currency} para la campaña "${reactivationData.campaign_name || 'Amigos de la Esperanza'}" ha sido reactivada con éxito.`
                                    : `Tu donación de ${finalAmount} ${currencyStr} ha sido recibida con éxito.`}
                            </p>
                            
                            {(() => {
                                localStorage.removeItem('donation_draft');
                                return null;
                            })()}
                            
                            <div className="flex justify-center gap-4">
                                <button
                                    onClick={resetFlow}
                                    className="px-6 py-3.5 bg-rosa-principal text-white rounded-xl font-bold hover:bg-opacity-90 transition shadow-md"
                                >
                                    Volver al inicio
                                </button>
                            </div>
                        </div>
                    )}
                    
                    {reactivationData && step === 1 && status !== 'paid' && (
                        <div className="bg-emerald-50 border border-emerald-200 p-5 rounded-2xl mb-6 shadow-sm">
                            <div className="flex items-start gap-3">
                                <div className="text-2xl text-emerald-600">🌟</div>
                                <div className="flex-1">
                                    <h4 className="font-bold text-emerald-900 text-base">¡Qué alegría tenerte de vuelta{reactivationData.donor_name ? `, ${reactivationData.donor_name}` : ''}!</h4>
                                    <p className="text-sm text-emerald-800 mt-1">
                                        Estás reanudando tu membresía recurrente de <strong>{reactivationData.amount} {reactivationData.currency}</strong> para la campaña <strong>{reactivationData.campaign_name || 'Amigos de la Esperanza'}</strong>.
                                    </p>
                                    {reactivationData.has_saved_card && (
                                        <div className="mt-4 pt-3 border-t border-emerald-200">
                                            <button
                                                onClick={handleOneClickReactivate}
                                                disabled={loading}
                                                className="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2 text-sm"
                                            >
                                                {loading ? 'Reactivando...' : '⚡ Reactivar con 1 Clic (Reusar Tarjeta Registrada)'}
                                            </button>
                                            <p className="text-xs text-center text-emerald-700 mt-2">O si prefieres cambiar de tarjeta, ingresa tus nuevos datos abajo:</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {hasDraft && !reactivationData && step === 1 && status !== 'paid' && (
                        <div className="bg-yellow-50 border border-yellow-200 p-4 rounded-xl flex items-center justify-between mb-6 shadow-sm">
                            <div className="flex items-center gap-3">
                                <div className="text-yellow-600">
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div className="text-sm text-yellow-800 font-medium">
                                    Tienes una donación pendiente. ¿Deseas recuperar tu borrador?
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <button onClick={handleRestoreDraft} className="text-xs px-3 py-1.5 bg-yellow-600 text-white font-bold rounded-lg hover:bg-yellow-700 transition">Completar Pago</button>
                                <button onClick={() => { localStorage.removeItem('donation_draft'); setHasDraft(false); }} className="text-xs px-3 py-1.5 bg-yellow-100 text-yellow-800 font-bold rounded-lg hover:bg-yellow-200 transition">Descartar</button>
                            </div>
                        </div>
                    )}

                    {/* --- STEP 1: DONATION & PAYMENT METHOD --- */}
                    <div className={step === 1 && status !== 'paid' ? 'block animate-fade-in' : 'hidden'}>
                        <h2 className="text-2xl font-bold text-gray-900 mb-1">
                            {reactivationData ? `Reactivar Membresía: ${reactivationData.campaign_name || 'Amigos de la Esperanza'}` : 'Tu donación'}
                        </h2>
                        <p className="text-gray-500 text-sm mb-8">
                            {reactivationData ? 'Confirma los datos para reactivar tu donación recurrente.' : 'Tu ayuda hace la diferencia. Elige el monto y la frecuencia de tu donación.'}
                        </p>

                        {/* Selector de Moneda y Monto */}
                        <div className="mb-6">
                            <div className="flex items-center justify-between mb-3">
                                <label className="block font-bold text-gray-800">Monto de la donación</label>
                                
                                {/* Selector de Moneda (Tabs) */}
                                {(!campaign || campaign.allowed_currencies === 'all') && paymentMethod === 'card' && !reactivationData && (
                                    <div className="flex bg-gray-100 p-1 rounded-xl gap-1">
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setCurrency('Bs');
                                                if (!customAmount) setSelectedPreset('100');
                                            }}
                                            className={`px-3 py-1 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 ${
                                                currency === 'Bs' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                                            }`}
                                        >
                                            <span>🇧🇴</span> Bs
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setCurrency('USD');
                                                if (!customAmount) setSelectedPreset('25');
                                            }}
                                            className={`px-3 py-1 text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 ${
                                                currency === 'USD' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                                            }`}
                                        >
                                            <span>🇺🇸</span> USD
                                        </button>
                                    </div>
                                )}
                                {paymentMethod === 'qr' && (
                                    <span className="text-xs text-gray-600 font-medium bg-gray-100 px-2.5 py-1 rounded-lg">
                                        🇧🇴 Bolivianos (BOB)
                                    </span>
                                )}
                            </div>

                            {/* Montos Predefinidos Inteligentes */}
                            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                                {PRESET_TIERS[currency].map((preset) => (
                                    <button
                                        key={preset.amount}
                                        type="button"
                                        onClick={() => {
                                            setSelectedPreset(preset.amount);
                                            setCustomAmount('');
                                        }}
                                        className={`py-3 px-2 rounded-xl text-center border-2 transition-all font-bold ${
                                            selectedPreset === preset.amount && !customAmount
                                                ? 'border-rosa-principal bg-rosa-claro text-rosa-principal shadow-sm'
                                                : 'border-gray-200 text-gray-700 hover:border-rosa-principal/50 bg-white'
                                        }`}
                                    >
                                        <div className="text-base sm:text-lg">{preset.label}</div>
                                    </button>
                                ))}
                            </div>

                            {/* Campo de Monto Personalizado */}
                            <div className="relative flex items-center">
                                <span className="absolute left-3.5 text-gray-400 font-bold text-sm select-none">
                                    {currency === 'USD' ? '$' : 'Bs'}
                                </span>
                                <input
                                    type="number"
                                    min="1"
                                    step="any"
                                    value={customAmount}
                                    onChange={(e) => {
                                        setCustomAmount(e.target.value);
                                        if (e.target.value) setSelectedPreset('');
                                    }}
                                    placeholder="Ingresar otro monto personalizado..."
                                    className={`w-full py-2.5 pl-11 pr-16 rounded-xl border-2 text-sm outline-none transition-all ${
                                        customAmount
                                            ? 'border-rosa-principal bg-rosa-claro text-rosa-principal font-bold'
                                            : 'border-gray-200 bg-white text-gray-800 focus:border-rosa-principal'
                                    }`}
                                />
                                <span className="absolute right-3.5 text-xs font-bold text-gray-400 uppercase select-none">
                                    {currency === 'USD' ? 'USD' : 'BOB'}
                                </span>
                            </div>
                        </div>

                        {/* Método de Pago (Tabs) */}
                        <div className="mb-6">
                            <label className="block font-bold text-gray-800 mb-3">Método de pago</label>
                            <div className="flex bg-gray-100 p-1 rounded-xl">
                                <button
                                    onClick={() => setPaymentMethod('card')}
                                    className={`flex-1 py-2 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2 ${paymentMethod === 'card' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                                >
                                    💳 Tarjeta Crédito/Débito
                                </button>
                                {!reactivationData && (!campaign || ((campaign.allowed_payment_methods === 'all' || campaign.allowed_payment_methods === 'qr_only') && campaign.allowed_frequencies !== 'monthly_only')) && (
                                    <button
                                        onClick={() => setPaymentMethod('qr')}
                                        className={`flex-1 py-2 text-sm font-bold rounded-lg transition-all flex items-center justify-center gap-2 ${paymentMethod === 'qr' ? 'bg-white text-rosa-principal shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                                    >
                                        📱 Código QR Simple
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Frecuencia (Ocultar/Bloquear si es QR o Reactivación) */}
                        <div className="mb-6">
                            <label className="block font-bold text-gray-800 mb-3 flex justify-between">
                                Frecuencia
                                {paymentMethod === 'qr' && <span className="text-xs text-orange-500 font-normal bg-orange-50 px-2 py-0.5 rounded">El QR Simple solo soporta donación única</span>}
                            </label>
                            <div className="grid grid-cols-2 gap-4">
                                <button
                                    onClick={() => setFrequency('monthly')}
                                    disabled={paymentMethod === 'qr'}
                                    className={`p-4 rounded-xl text-left border-2 transition-all relative ${
                                        paymentMethod === 'qr' ? 'opacity-50 cursor-not-allowed border-gray-100' :
                                        frequency === 'monthly' ? 'border-rosa-principal bg-rosa-claro' : 'border-gray-200 hover:border-rosa-principal/50'
                                    }`}
                                >
                                    {frequency === 'monthly' && <div className="absolute top-2 right-2 w-4 h-4 bg-rosa-principal text-white rounded-full flex items-center justify-center text-[10px]">✓</div>}
                                    <div className="font-bold text-gray-900 flex items-center gap-2">
                                        <svg className="w-4 h-4 text-rosa-principal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Mensual
                                    </div>
                                    <div className="text-xs text-gray-500 mt-1">Se cobrará cada mes automáticamente</div>
                                </button>
                                {!reactivationData && (!campaign || campaign.allowed_frequencies === 'all' || campaign.allowed_frequencies === 'once_only') && (
                                    <button
                                        onClick={() => setFrequency('once')}
                                        className={`p-4 rounded-xl text-left border-2 transition-all relative ${
                                            frequency === 'once' ? 'border-rosa-principal bg-rosa-claro' : 'border-gray-200 hover:border-rosa-principal/50'
                                        }`}
                                    >
                                        {frequency === 'once' && <div className="absolute top-2 right-2 w-4 h-4 bg-rosa-principal text-white rounded-full flex items-center justify-center text-[10px]">✓</div>}
                                        <div className="font-bold text-gray-900">Una sola vez</div>
                                        <div className="text-xs text-gray-500 mt-1">Cobro único al momento</div>
                                    </button>
                                )}
                            </div>
                        </div>

                        {/* Highlight box for recurring */}
                        {frequency === 'monthly' && paymentMethod === 'card' && (
                            <div className="bg-gray-50 border border-gray-100 p-4 rounded-xl flex gap-4 items-start mb-8">
                                <div className="text-rosa-principal mt-1">
                                    <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                </div>
                                <div>
                                    <h4 className="font-bold text-gray-900 text-sm">Tu donación será recurrente y automática</h4>
                                    <p className="text-xs text-gray-500 mt-1">El cobro de tu tarjeta se realizará automáticamente. Puedes cancelar tu suscripción cuando quieras desde tu perfil.</p>
                                </div>
                            </div>
                        )}

                        {/* ATC Credit Card Inputs */}
                        <div className={paymentMethod === 'card' ? 'block mb-8' : 'hidden'}>
                            <AtcCreditCardForm 
                                ref={cardFormRef} 
                                onStatusChange={(step, msg) => setAtcStatusMsg(msg)}
                                onSuccess={() => setStatus('paid')}
                                onError={(err) => {
                                    setError(err.message);
                                    setStep(1); // Go back if error
                                }}
                            />
                        </div>
                        
                        {/* Mensaje QR */}
                        <div className={paymentMethod === 'qr' ? 'block mb-8 p-4 bg-blue-50 border border-blue-100 rounded-xl flex gap-3' : 'hidden'}>
                            <div className="text-blue-600 text-xl">📱</div>
                            <div className="text-sm text-blue-800">
                                <span className="font-bold">Pago rápido con código QR Simple.</span>
                                <br />Generaremos tu código único al finalizar el siguiente paso para que lo escanees desde tu banca móvil (solo bancos de Bolivia).
                            </div>
                        </div>

                        <button
                            onClick={handleNextToStep2}
                            className="w-full py-4 bg-rosa-principal text-white font-bold rounded-xl hover:bg-rosa-principal transition duration-300 shadow-md flex items-center justify-center gap-2"
                        >
                            {paymentMethod === 'card' 
                                ? `💳 Pagar ${finalAmount > 0 ? `${finalAmount} ${currencyStr}` : ''}`
                                : (user || isAnonymous 
                                    ? `📱 Generar Código QR ${finalAmount > 0 ? `(${finalAmount} ${currencyStr})` : ''}`
                                    : 'Continuar a Tus Datos')}
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    {/* --- STEP 2: IDENTITY --- */}
                    <div className={step === 2 ? 'block animate-fade-in' : 'hidden'}>
                        <h2 className="text-2xl font-bold text-gray-900 mb-1">Tus datos de donante</h2>
                        <p className="text-gray-500 text-sm mb-6">Completa tu nombre y correo para enviarte la confirmación y tu certificado de donación.</p>

                        <div className="flex bg-gray-100 p-1 rounded-xl mb-8">
                            <button
                                onClick={() => setIsAnonymous(false)}
                                className={`flex-1 py-3 text-sm font-bold rounded-lg transition-all ${!isAnonymous ? 'bg-white text-rosa-principal shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                            >
                                Identificarme (Recomendado)
                            </button>
                            <button
                                onClick={() => setIsAnonymous(true)}
                                className={`flex-1 py-3 text-sm font-bold rounded-lg transition-all ${isAnonymous ? 'bg-white text-gray-800 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                            >
                                Donar anónimamente
                            </button>
                        </div>

                        {!isAnonymous && (
                            <div className="space-y-4 mb-8">
                                {!user ? (
                                    <div className="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-4 flex justify-between items-center">
                                        <div className="text-sm text-blue-800">
                                            Ingresa tus datos para enviarte tu recibo digital.
                                        </div>
                                        <a href="/login" className="text-sm font-bold text-azul-marino underline">
                                            Iniciar sesión si ya tienes cuenta
                                        </a>
                                    </div>
                                ) : (
                                    <div className="bg-emerald-50 border border-emerald-200 p-4 rounded-xl mb-4 text-sm text-emerald-800 font-medium flex items-center gap-2 shadow-sm">
                                        <svg className="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span>Sesión iniciada como: <strong>{user.name}</strong> ({user.email})</span>
                                    </div>
                                )}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-bold text-gray-700 mb-1">Nombre Completo</label>
                                        <input
                                            type="text"
                                            value={donorName}
                                            onChange={(e) => setDonorName(e.target.value)}
                                            className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-rosa-claro outline-none"
                                            placeholder="Juan Pérez"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold text-gray-700 mb-1">Correo Electrónico</label>
                                        <input
                                            type="email"
                                            value={donorEmail}
                                            onChange={(e) => setDonorEmail(e.target.value)}
                                            className="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-rosa-claro outline-none"
                                            placeholder="correo@ejemplo.com"
                                        />
                                    </div>
                                </div>
                            </div>
                        )}

                        {isAnonymous && (
                            <div className="bg-gray-50 border border-gray-200 p-6 rounded-xl mb-8">
                                <h3 className="font-bold text-gray-800 text-sm mb-2">Donación Anónima</h3>
                                <p className="text-gray-600 text-sm">
                                    Has elegido realizar tu donación de forma anónima. No guardaremos tus datos en nuestro portal ni emitiremos un certificado a tu nombre.
                                </p>
                                {paymentMethod === 'card' && (
                                    <p className="text-blue-600 text-xs mt-3 bg-blue-50 p-2 rounded">
                                        💡 <strong>Nota:</strong> Los datos de tarjeta que ingresaste (Nombre y Correo) en el paso anterior solo serán utilizados por el banco de forma privada para validar la seguridad de la transacción.
                                    </p>
                                )}
                            </div>
                        )}

                        <div className="flex gap-4">
                            <button 
                                onClick={() => setStep(1)}
                                disabled={loading}
                                className="w-1/3 py-4 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition border border-gray-200"
                            >
                                Volver
                            </button>
                            <button
                                onClick={handleFinalize}
                                disabled={loading}
                                className="w-2/3 py-4 bg-rosa-principal text-white font-bold rounded-xl hover:bg-rosa-principal transition disabled:bg-gray-400 shadow-md flex items-center justify-center gap-2"
                            >
                                {loading ? 'Procesando...' : (paymentMethod === 'card' ? `💳 Pagar ${finalAmount} ${currencyStr}` : '📱 Generar QR')}
                            </button>
                        </div>
                    </div>

                    {/* --- STEP 3: CONFIRMATION / QR / SUCCESS --- */}
                    <div className={step === 3 ? 'block animate-fade-in' : 'hidden'}>
                        
                        {/* ESTADO ATC CARGANDO */}
                        {paymentMethod === 'card' && status !== 'paid' && !error && (
                            <div className="text-center py-12">
                                <div className="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-600 mx-auto mb-6"></div>
                                <h2 className="text-2xl font-bold text-gray-900 mb-2">Procesando Pago Seguro</h2>
                                <p className="text-gray-500">{atcStatusMsg || 'Conectando con Cybersource...'}</p>
                            </div>
                        )}

                        {/* ESTADO QR GENERADO */}
                        {paymentMethod === 'qr' && qrData && status !== 'paid' && (
                            <div className="text-center">
                                <h2 className="text-2xl font-bold text-gray-900 mb-2">Escanea tu código QR</h2>
                                <p className="text-sm text-gray-600 mb-8 font-sans">Abre tu aplicación bancaria móvil favorita y escanea el código para completar tu aporte de <strong>{finalAmount} {currencyStr}</strong>.</p>
                                
                                <div className="flex flex-col items-center justify-center mb-6">
                                    <NextImage
                                        src={qrData.qr_image.startsWith('data:') ? qrData.qr_image : `data:image/png;base64,${qrData.qr_image}`}
                                        alt="QR de Pago"
                                        width={260}
                                        height={260}
                                        className="border-8 border-white shadow-xl rounded-xl mb-4"
                                    />
                                    <a
                                        href={qrData.qr_image.startsWith('data:') ? qrData.qr_image : `data:image/png;base64,${qrData.qr_image}`}
                                        download="QR_Donacion_Nuestra_Esperanza.png"
                                        className="flex items-center gap-2 text-sm font-bold text-azul-marino bg-blue-50 px-4 py-2 rounded-lg hover:bg-blue-100 transition border border-blue-200"
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Descargar QR
                                    </a>
                                </div>
                                
                                <div className="flex justify-center items-center gap-3 mb-6 bg-blue-50 p-4 rounded-xl max-w-sm mx-auto">
                                    <div className="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-blue-600"></div>
                                    <span className="text-sm font-bold text-blue-800">Esperando confirmación de pago...</span>
                                </div>

                                {qrData.mock && (
                                    <div className="mb-4">
                                        <button
                                            onClick={handleSimulatePayment}
                                            disabled={simulating}
                                            className={`text-xs px-4 py-2 rounded-full font-mono transition ${simulating ? 'bg-gray-300' : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'}`}
                                        >
                                            {simulating ? 'Simulando...' : '⚡ DEV: Simular Pago Exitoso'}
                                        </button>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                </div>

                {/* RIGHT COLUMN: DONATION SUMMARY (Sticky) */}
                <div className="hidden md:block md:col-span-5 bg-gray-50 border-l border-gray-100 p-8">
                    <div className="sticky top-8">
                        <h3 className="font-bold text-gray-900 text-lg mb-6">Resumen de tu donación</h3>
                        
                        <div className="bg-gray-200 rounded-xl w-full h-40 mb-6 overflow-hidden relative">
                            <NextImage src="/IMG/hero_help.jpg" alt="Transformamos futuros" fill style={{ objectFit: 'cover' }} />
                            <div className="absolute inset-0 bg-gradient-to-tr from-rosa-principal to-azul-marino opacity-60"></div>
                            <div className="absolute inset-0 flex items-center justify-center text-white p-6 text-center">
                                <span className="font-bold text-xl drop-shadow-md">&quot;Con tu ayuda, transformamos futuros&quot;</span>
                            </div>
                        </div>

                        <div className="space-y-4 text-sm text-gray-700 mb-8 border-b border-gray-200 pb-8">
                            <div className="flex justify-between items-start gap-4">
                                <span className="text-gray-500 flex items-center gap-2">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    Institución
                                </span>
                                <span className="font-bold text-right">Fundación<br/>Nuestra Esperanza</span>
                            </div>
                            
                            <div className="flex justify-between items-center">
                                <span className="text-gray-500 flex items-center gap-2">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Monto
                                </span>
                                <span className="font-bold">{finalAmount} {currencyStr}</span>
                            </div>

                            <div className="flex justify-between items-center">
                                <span className="text-gray-500 flex items-center gap-2">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Frecuencia
                                </span>
                                <span className="font-bold">{frequency === 'monthly' ? 'Mensual' : 'Única vez'}</span>
                            </div>

                            <div className="flex justify-between items-center">
                                <span className="text-gray-500 flex items-center gap-2">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Método
                                </span>
                                <span className="font-bold">{paymentMethod === 'card' ? 'Tarjeta (Cybersource)' : 'QR (BNB/Simple)'}</span>
                            </div>
                        </div>

                        <div className="flex justify-between items-center mb-8">
                            <span className="font-bold text-gray-900 text-lg">Total por cobro</span>
                            <span className="font-bold text-rosa-principal text-2xl">{finalAmount} {currencyStr}</span>
                        </div>

                        <div className="bg-white p-4 rounded-xl border border-gray-100 flex items-start gap-4">
                            <div className="text-rosa-principal">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h4 className="font-bold text-gray-900 text-sm">Tu donación es 100% segura</h4>
                                <p className="text-xs text-gray-500 mt-1">Nuestra plataforma cuenta con seguridad SSL y validación bancaria {paymentMethod === 'card' ? '3D Secure (EMVCo)' : 'encriptada'}.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    );
};

// Main Export wrapping in Suspense to resolve useSearchParams build issues in Next.js 14
const DonationForm: React.FC<DonationFormProps> = (props) => {
    return (
        <Suspense fallback={<div className="bg-white p-8 rounded-xl text-center shadow-lg">Cargando formulario seguro...</div>}>
            <DonationFormContent {...props} />
        </Suspense>
    );
}

export default DonationForm;
