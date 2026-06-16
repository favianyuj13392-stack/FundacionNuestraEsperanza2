"use client";
import React, { useState, useEffect } from 'react';
import { donationService, DonationTier, QrResponse } from '@/services/donationService';
import { useAuth } from '@/context/AuthContext';
import { useRouter, useSearchParams } from 'next/navigation';
import NextImage from 'next/image';

interface DonationFormProps {
    onClose?: () => void;
    isInModal?: boolean;
    campaignIdProp?: number;
}

const DonationForm: React.FC<DonationFormProps> = ({ onClose, isInModal = false, campaignIdProp }) => {
    const { user } = useAuth();
    const router = useRouter();
    const searchParams = useSearchParams();
    const campaignIdParam = searchParams.get('campaign_id');
    const campaignId = campaignIdProp !== undefined ? campaignIdProp : (campaignIdParam ? parseInt(campaignIdParam) : undefined);

    // States for Multi-step Flow
    const [step, setStep] = useState<'amount' | 'identity' | 'details' | 'qr' | 'success'>('amount');
    const [frequency, setFrequency] = useState<'once' | 'monthly'>('once');
    
    // Data States
    const [tiers, setTiers] = useState<DonationTier[]>([]);
    const [selectedTier, setSelectedTier] = useState<number | null>(null);
    const [customAmount, setCustomAmount] = useState<string>('');
    const [isAnonymous, setIsAnonymous] = useState(true);
    
    // Donor Details
    const [donorName, setDonorName] = useState('');
    const [donorEmail, setDonorEmail] = useState('');
    const [donorCi, setDonorCi] = useState('');
    const [donorPhone, setDonorPhone] = useState('');
    
    // QR & Status
    const [loading, setLoading] = useState(false);
    const [qrData, setQrData] = useState<QrResponse | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [status, setStatus] = useState<string | null>(null);
    const [simulating, setSimulating] = useState(false);

    // Initial Options
    useEffect(() => {
        const fetchOptions = async () => {
            try {
                const data = await donationService.getOptions();
                setTiers(data);
                // if (data.length > 0) setSelectedTier(data[0].id); // Default select first - REMOVED per user request
            } catch (err) {
                console.error("Failed to fetch donation tiers", err);
                // Fallback defaults if API fails
                setTiers([
                    { id: 1, amount: "50", label: "Donación", currency_id: 1 },
                    { id: 2, amount: "100", label: "Gran Ayuda", currency_id: 1 },
                    { id: 3, amount: "200", label: "Padrino", currency_id: 1 },
                ]);
            }
        };
        fetchOptions();
    }, []);

    // Restore pending donation after login
    useEffect(() => {
        const pending = localStorage.getItem('pendingDonation');
        if (pending && user) {
            try {
                const { tier, amount, frequency: savedFreq } = JSON.parse(pending);
                if (tier) setSelectedTier(tier);
                if (amount) setCustomAmount(amount);
                if (savedFreq) setFrequency(savedFreq);
                setStep('details');
                localStorage.removeItem('pendingDonation');
            } catch (e) {
                console.error("Error parsing pending donation", e);
            }
        }
    }, [user]);

    // Pre-fill user data
    useEffect(() => {
        if (user) {
            if (user.name) setDonorName(user.name);
            if (user.email) setDonorEmail(user.email);
        }
    }, [user]);

    // Polling for status
    useEffect(() => {
        let interval: NodeJS.Timeout;
        const POLLING_TIMEOUT = 15 * 60 * 1000;
        const startTime = Date.now();

        if (step === 'qr' && qrData && qrData.qr_id && status !== 'paid') {
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
                        setStep('success');
                        clearInterval(interval);
                    }
                } catch (err) {
                    console.error("Polling error", err);
                }
            }, 5000);
        }
        return () => clearInterval(interval);
    }, [step, qrData, status]);

    // --- Handlers ---

    const handleAmountSelect = () => {
        setError(null);
        if (!selectedTier && (!customAmount || parseFloat(customAmount) <= 0)) {
            setError("Por favor selecciona un monto o introduce uno válido.");
            return;
        }
        setStep('identity');
    };

    const handleIdentitySelect = (anonymous: boolean) => {
        setIsAnonymous(anonymous);
        
        if (anonymous) {
            generateQr(true);
        } else {
            if (!user) {
                const state = {
                    tier: selectedTier,
                    amount: customAmount,
                    frequency: frequency
                };
                localStorage.setItem('pendingDonation', JSON.stringify(state));
                // Use window.location as fallback if router behaviour is inconsistent
                router.push('/login?redirect=back'); 
                return;
            }
            setStep('details');
        }
    };

    const submitDetails = () => {
        if (!donorName.trim() || !donorCi.trim() || !donorPhone.trim() || (frequency === 'monthly' && !donorEmail.trim())) {
            setError("Por favor completa todos los campos requeridos.");
            return;
        }
        generateQr(false);
    };

    const generateQr = async (anonymous: boolean) => {
        setLoading(true);
        setError(null);
        setQrData(null);
        
        try {
            let res: QrResponse;
            const details = anonymous ? undefined : { name: donorName, ci: donorCi, phone: donorPhone, email: donorEmail };
            
            if (frequency === 'monthly') {
                if (!details || !details.email) throw new Error("Faltan datos requeridos para donación mensual");
                const finalAmount = selectedTier ? tiers.find(t => t.id === selectedTier)?.amount : customAmount;
                res = await donationService.requestSubscriptionQr(parseFloat(finalAmount || '0'), details, campaignId);
            } else {
                if (selectedTier) {
                    res = await donationService.requestQr(selectedTier, undefined, anonymous, details, campaignId);
                } else {
                    res = await donationService.requestQr(undefined, parseFloat(customAmount), anonymous, details, campaignId);
                }
            }
            
            setQrData(res);
            setStep('qr');

        } catch (err) {
            const message = err instanceof Error ? err.message : "Ocurrió un error inesperado al generar el QR.";
            setError(message);
            setStep('amount');
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
        setStep('amount');
        setStatus(null);
        setQrData(null);
        // Keep selection/input as is for generic reset, or clear it?
        // Let's keep defaults
        if (!user) {
            setDonorName('');
            setDonorEmail('');
            setDonorCi('');
            setDonorPhone('');
        }
        // If in modal, maybe close instead of reset?
        // if (isInModal && onClose) onClose();
    };

    // --- Renders ---

    if (step === 'success') {
        return (
            <div className={`text-center p-8 bg-green-50 rounded-xl shadow-md animate-fade-in ${isInModal ? '' : 'max-w-md mx-auto'}`}>
                <h2 className="text-3xl font-bold text-green-700 mb-4 font-title">¡Gracias por tu donación!</h2>
                <p className="text-gray-700 font-sans mb-6">Tu aporte ha sido recibido exitosamente.</p>
                { !isAnonymous && (
                    <div className="mb-6">
                        <p className="text-sm text-gray-500 mb-2">Hemos generado tu certificado.</p>
                        <a 
                            href="/perfil" 
                            className="text-rosa-principal font-bold hover:underline"
                        >
                            Ver en mi Historial
                        </a>
                    </div>
                )}
                <div className="flex flex-col gap-3">
                    <button
                        onClick={resetFlow}
                        className="px-6 py-3 bg-green-600 text-white rounded-full font-bold hover:bg-green-700 transition font-button"
                    >
                        Realizar otra donación
                    </button>
                    {isInModal && onClose && (
                         <button onClick={onClose} className="text-gray-500 text-sm hover:underline">Cerrar</button>
                    )}
                </div>
            </div>
        );
    }

    return (
        <div className={`bg-white p-6 md:p-8 rounded-xl ${isInModal ? '' : 'shadow-xl max-w-md mx-auto'}`}>
            {/* Header */}
            <div className="relative mb-6 text-center">
                 {isInModal && onClose && (
                    <button onClick={onClose} className="absolute -top-2 -right-2 text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
                )}
                <h2 className="text-3xl font-bold text-azul-marino font-title">
                    {step === 'amount' && "Elige tu Aporte"}
                    {step === 'identity' && "¿Cómo deseas donar?"}
                    {step === 'details' && "Tus Datos"}
                    {step === 'qr' && "Escanea el QR"}
                </h2>
            </div>

            {/* Error */}
            {error && <p className="text-red-500 text-sm mb-4 text-center font-sans bg-red-50 p-2 rounded">{error}</p>}

            {/* STEP 1: AMOUNT */}
            {step === 'amount' && (
                <div className="animate-fade-in">
                    <div className="flex bg-gray-100 p-1 rounded-full mb-6 relative">
                        <button
                            onClick={() => setFrequency('once')}
                            className={`flex-1 py-2 text-sm font-bold rounded-full transition-all duration-300 z-10 ${frequency === 'once' ? 'bg-white text-rosa-principal shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            Una sola vez
                        </button>
                        <button
                            onClick={() => setFrequency('monthly')}
                            className={`flex-1 py-2 text-sm font-bold rounded-full transition-all duration-300 z-10 ${frequency === 'monthly' ? 'bg-white text-rosa-principal shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            Mensualmente
                        </button>
                    </div>

                    <div className="flex justify-center space-x-3 mb-6">
                        {tiers.map((tier) => (
                            <button
                                key={tier.id}
                                onClick={() => { setSelectedTier(tier.id); setCustomAmount(''); }}
                                className={`px-4 py-3 rounded-full font-bold text-lg transition-all transform duration-300 font-sans ${
                                    selectedTier === tier.id
                                        ? 'bg-rosa-principal text-white scale-110 shadow-lg'
                                        : 'bg-gray-100 text-gray-700 hover:bg-rosa-claro'
                                }`}
                            >
                                {tier.amount} Bs
                            </button>
                        ))}
                    </div>
                    
                    <div className="mb-6">
                        <input
                            type="number"
                            value={customAmount}
                            onChange={(e) => { 
                                setCustomAmount(e.target.value); 
                                setSelectedTier(null); // Deselect tier immediately when typing
                            }}
                            placeholder="Otro monto (Bs)"
                            className="w-full p-3 text-center rounded-full border-2 border-gray-300 focus:border-rosa-principal focus:ring-rosa-principal font-sans outline-none transition"
                        />
                    </div>

                    <button
                        onClick={handleAmountSelect}
                        className="w-full py-4 bg-verde-lima text-white font-bold rounded-full text-xl hover:bg-verde-lima-claro transition duration-300 font-button shadow-md"
                    >
                        CONTINUAR DONACIÓN
                    </button>
                    {!isInModal && (
                        <p className="text-xs text-gray-400 text-center mt-4">Tu donación es segura y directa.</p>
                    )}
                </div>
            )}

            {/* STEP 2: IDENTITY */}
            {step === 'identity' && (
                <div className="flex flex-col gap-4 animate-fade-in">
                    <button
                        onClick={() => handleIdentitySelect(false)}
                        className="w-full py-4 bg-rosa-principal text-white font-bold rounded-xl hover:bg-opacity-90 transition flex items-center justify-center gap-3 shadow-md"
                    >
                        <span className="text-2xl">📝</span> 
                        <div className="text-left">
                            <div className="text-lg leading-tight">Quiero identificarme</div>
                            <div className="text-xs opacity-90 font-normal">Recibiré certificado de donación</div>
                        </div>
                    </button>
                    
                    { (!user && frequency !== 'monthly') && (
                        <button
                            onClick={() => handleIdentitySelect(true)}
                            className="w-full py-4 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition flex items-center justify-center gap-3"
                        >
                            <span className="text-2xl">🕵️</span> 
                            <div className="text-left">
                                <div className="text-lg leading-tight">Donar Anónimamente</div>
                                <div className="text-xs opacity-80 font-normal">Sin certificado</div>
                            </div>
                        </button>
                    ) }

                    <button 
                        onClick={() => setStep('amount')}
                        className="text-sm text-gray-400 mt-2 hover:text-gray-600 underline"
                    >
                        ← Volver a elegir monto
                    </button>
                </div>
            )}

            {/* STEP 3: DETAILS */}
            {step === 'details' && (
                <div className="animate-fade-in">
                    <div className="space-y-4 mb-8">
                        <div>
                            <label className="block text-sm font-bold text-gray-700 ml-1 mb-1">Nombre Completo</label>
                            <input
                                type="text"
                                value={donorName}
                                onChange={(e) => setDonorName(e.target.value)}
                                className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-rosa-principal outline-none"
                                placeholder="Ej: Juan Pérez"
                            />
                        </div>
                        {frequency === 'monthly' && (
                        <div>
                            <label className="block text-sm font-bold text-gray-700 ml-1 mb-1">Correo Electrónico</label>
                            <input
                                type="email"
                                value={donorEmail}
                                onChange={(e) => setDonorEmail(e.target.value)}
                                className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-rosa-principal outline-none"
                                placeholder="Ej: correo@ejemplo.com"
                            />
                        </div>
                        )}
                        <div>
                            <label className="block text-sm font-bold text-gray-700 ml-1 mb-1">CI / Documento ID</label>
                            <input
                                type="text"
                                value={donorCi}
                                onChange={(e) => setDonorCi(e.target.value)}
                                className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-rosa-principal outline-none"
                                placeholder="Ej: 8462015 LP"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-bold text-gray-700 ml-1 mb-1">Celular</label>
                            <input
                                type="text"
                                value={donorPhone}
                                onChange={(e) => setDonorPhone(e.target.value)}
                                className="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-rosa-principal outline-none"
                                placeholder="Ej: 777123456"
                            />
                        </div>
                    </div>

                    <button
                        onClick={submitDetails}
                        disabled={loading}
                        className="w-full py-4 bg-azul-marino text-white font-bold rounded-full hover:bg-opacity-90 transition disabled:bg-gray-400 font-button text-lg"
                    >
                        {loading ? 'Generando...' : 'GENERAR QR'}
                    </button>
                    
                    <button 
                        onClick={() => setStep('identity')}
                        disabled={loading}
                        className="w-full text-center text-sm text-gray-400 mt-4 hover:underline"
                    >
                        ← Volver
                    </button>
                </div>
            )}

            {/* STEP 4: QR */}
            {step === 'qr' && qrData && (
                <div className="animate-fade-in text-center">
                    <p className="text-sm text-gray-600 mb-4 font-sans">Escanea este código desde tu aplicación bancaria:</p>
                    
                    <div className="flex justify-center mb-6">
                        <NextImage
                            src={qrData.qr_image.startsWith('data:') ? qrData.qr_image : `data:image/png;base64,${qrData.qr_image}`}
                            alt="QR de Pago"
                            width={240}
                            height={240}
                            className="border-8 border-white shadow-2xl rounded-xl"
                            style={{ maxWidth: '100%', height: 'auto' }}
                        />
                    </div>
                    
                    <div className="flex justify-center mb-6">
                        <a 
                            href={qrData.qr_image.startsWith('data:') ? qrData.qr_image : `data:image/png;base64,${qrData.qr_image}`} 
                            download="qr_donacion_fne.png"
                            className="text-rosa-principal font-bold flex items-center gap-2 hover:underline bg-rosa-50 px-4 py-2 rounded-full text-sm"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Descargar QR
                        </a>
                    </div>

                    <div className="flex justify-center items-center gap-3 mb-6 bg-gray-50 p-3 rounded-lg">
                        <div className="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-rosa-principal"></div>
                        <span className="text-sm font-bold text-gray-600">Esperando confirmación de pago...</span>
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
                    
                    <button 
                        onClick={() => {
                            setStep('amount');
                            setQrData(null);
                        }}
                        className="text-sm text-red-500 hover:text-red-700 underline"
                    >
                        Cancelar y volver
                    </button>
                </div>
            )}
        </div>
    );
};

export default DonationForm;