"use client";
import React, { useEffect, useState } from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/navigation';
import { API_BASE_URL } from '@/utils/apiBaseUrl';
import { donationService, Donation } from '@/services/donationService';
import DonationModal from '@/components/DonationModal';

export default function ProfilePage() {
    const { user, logout, login, token } = useAuth();
    const router = useRouter();
    
    // --- States for Sections ---
    const [activeTab, setActiveTab] = useState<'profile' | 'security' | 'history'>('profile');
    
    // History
    const [donations, setDonations] = useState<Donation[]>([]);
    const [loadingHistory, setLoadingHistory] = useState(true);

    // Profile Form
    const [name, setName] = useState('');
    const [lastName, setLastName] = useState('');
    const [email, setEmail] = useState('');
    const [ci, setCi] = useState('');
    const [profileMessage, setProfileMessage] = useState<{ type: 'success' | 'error', text: string } | null>(null);
    const [loadingProfile, setLoadingProfile] = useState(false);

    // Password Form
    const [currentPassword, setCurrentPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmPassword, setConfirmPassword] = useState('');
    const [passwordMessage, setPasswordMessage] = useState<{ type: 'success' | 'error', text: string } | null>(null);
    const [loadingPassword, setLoadingPassword] = useState(false);

    const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);

    // Initial Load & Auth Check
    useEffect(() => {
        if (!user) {
            // router.push('/login'); // Handled by AuthContext usually, but safe to keep
            return;
        }
        // Fill profile form
        setName(user.name || '');
        setLastName(user.last_name || '');
        setEmail(user.email || '');
        setCi(user.ci || '');
        
        // DEBUG: Check user roles
        console.log('User data:', user);
        console.log('User roles:', user.roles);

        // Load history
        const fetchDonations = async () => {
             // Only fetch if tab is history? Or always? Let's fetch on mount or when tab changes if heavy.
             // For simplicity, fetch on mount but show spinner in tab.
            try {
                const data = await donationService.getMyDonations();
                setDonations(data);
            } catch (err) {
                console.error("Error fetching donations", err);
            } finally {
                setLoadingHistory(false);
            }
        };
        fetchDonations();
    }, [user, router]);


    // --- Handlers ---

    const handleUpdateProfile = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoadingProfile(true);
        setProfileMessage(null);
        
        try {
            const API_URL = API_BASE_URL;
            const res = await fetch(`${API_URL}/api/auth/update-profile`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ name, last_name: lastName, email, ci })
            });
            const data = await res.json();
            
            if (res.ok) {
                setProfileMessage({ type: 'success', text: 'Perfil actualizado correctamente.' });
                // Update local context
                // We need the token to remain same? Yes.
                if (token) login(data.data, token); 
            } else {
                setProfileMessage({ type: 'error', text: data.message || 'Error al actualizar.' });
            }
        } catch {
            setProfileMessage({ type: 'error', text: 'Error de conexión.' });
        } finally {
            setLoadingProfile(false);
        }
    };

    const handleChangePassword = async (e: React.FormEvent) => {
        e.preventDefault();
        if (newPassword !== confirmPassword) {
            setPasswordMessage({ type: 'error', text: 'las contraseñas nuevas no coinciden.' });
            return;
        }
        setLoadingPassword(true);
        setPasswordMessage(null);

        try {
            const API_URL = API_BASE_URL;
            const res = await fetch(`${API_URL}/api/auth/change-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({ current_password: currentPassword, new_password: newPassword, new_password_confirmation: confirmPassword })
            });
            const data = await res.json();

            if (res.ok) {
                setPasswordMessage({ type: 'success', text: 'Contraseña actualizada. Por favor inicia sesión nuevamente.' });
                setTimeout(() => {
                    logout();
                    router.push('/login');
                }, 2000);
            } else {
                setPasswordMessage({ type: 'error', text: data.message || 'Error al cambiar contraseña.' });
            }
        } catch {
            setPasswordMessage({ type: 'error', text: 'Error de conexión.' });
        } finally {
            setLoadingPassword(false);
        }
    };

    const handleLogout = () => {
        router.push('/');
        // Use setTimeout to ensure navigation starts before clearing state
        setTimeout(() => {
            logout();
        }, 100);
    };

    if (!user) return null;

    return (
        <main className="bg-gray-50 min-h-screen flex flex-col">
            <Navbar onOpenDonationModal={() => setIsDonationModalOpen(true)} />

            <div className="container mx-auto px-4 md:px-6 py-12 flex-grow">
                
                <div className="max-w-6xl mx-auto md:flex gap-8">
                    {/* Sidebar Tabs */}
                    <div className="md:w-1/4 mb-8 md:mb-0">
                        <div className="bg-white rounded-xl shadow-md overflow-hidden">
                            <div className="p-6 bg-azul-marino text-white text-center">
                                <div className="w-20 h-20 bg-rosa-principal rounded-full mx-auto flex items-center justify-center text-3xl font-bold mb-3">
                                    {user.name.charAt(0).toUpperCase()}
                                </div>
                                <h2 className="font-bold text-lg truncate">{user.name}</h2>
                                <p className="text-sm opacity-80 truncate">{user.email}</p>
                            </div>
                            <nav className="flex flex-col p-4 space-y-2">
                                <button 
                                    onClick={() => setActiveTab('profile')}
                                    className={`text-left px-4 py-3 rounded-lg font-bold transition flex items-center gap-3 ${activeTab === 'profile' ? 'bg-celeste-claro text-azul-marino' : 'text-gray-600 hover:bg-gray-50'}`}
                                >
                                    <span>👤</span> Mi Perfil
                                </button>
                                <button 
                                    onClick={() => setActiveTab('security')}
                                    className={`text-left px-4 py-3 rounded-lg font-bold transition flex items-center gap-3 ${activeTab === 'security' ? 'bg-celeste-claro text-azul-marino' : 'text-gray-600 hover:bg-gray-50'}`}
                                >
                                    <span>🔒</span> Seguridad
                                </button>
                                <button 
                                    onClick={() => setActiveTab('history')}
                                    className={`text-left px-4 py-3 rounded-lg font-bold transition flex items-center gap-3 ${activeTab === 'history' ? 'bg-celeste-claro text-azul-marino' : 'text-gray-600 hover:bg-gray-50'}`}
                                >
                                    <span>📜</span> Historial Donaciones
                                </button>
                                
                                {/* Admin Panel Button - Only visible for admin users */}
                                {user.roles && user.roles.includes('admin') && (
                                    <a 
                                        href={`${API_BASE_URL}/admin`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-left px-4 py-3 rounded-lg font-bold transition flex items-center gap-3 text-purple-600 hover:bg-purple-50"
                                    >
                                        <span>⚙️</span> Panel Admin
                                    </a>
                                )}
                                
                                <hr className="my-2"/>
                                <button 
                                    onClick={handleLogout}
                                    className="text-left px-4 py-3 rounded-lg font-bold text-red-500 hover:bg-red-50 transition flex items-center gap-3"
                                >
                                    <span>🚪</span> Cerrar Sesión
                                </button>
                            </nav>
                        </div>
                    </div>

                    {/* Main Content */}
                    <div className="md:w-3/4">
                        
                        {/* PROFILE TAB */}
                        {activeTab === 'profile' && (
                            <div className="bg-white rounded-xl shadow-md p-8 animate-fade-in">
                                <h1 className="text-2xl font-bold text-azul-marino font-title mb-6 border-b pb-4">Editar Perfil</h1>
                                {profileMessage && (
                                    <div className={`p-4 rounded mb-6 ${profileMessage.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                        {profileMessage.text}
                                    </div>
                                )}
                                <form onSubmit={handleUpdateProfile} className="space-y-6 max-w-lg">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label className="block text-sm font-bold text-gray-700 mb-1">Nombre</label>
                                            <input type="text" value={name} onChange={e => setName(e.target.value)} className="w-full border rounded px-3 py-2 focus:ring-rosa-principal" required />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-bold text-gray-700 mb-1">Apellidos</label>
                                            <input type="text" value={lastName} onChange={e => setLastName(e.target.value)} className="w-full border rounded px-3 py-2 focus:ring-rosa-principal" />
                                        </div>
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico</label>
                                        <input type="email" value={email} onChange={e => setEmail(e.target.value)} className="w-full border rounded px-3 py-2 focus:ring-rosa-principal" required />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 mb-1">Documento ID / CI</label>
                                        <input type="text" value={ci} onChange={e => setCi(e.target.value)} className="w-full border rounded px-3 py-2 focus:ring-rosa-principal" placeholder="Opcional" />
                                    </div>
                                    <button 
                                        type="submit" 
                                        disabled={loadingProfile}
                                        className="bg-azul-marino text-white px-8 py-3 rounded-full font-bold hover:bg-opacity-90 transition disabled:bg-gray-400 font-button"
                                    >
                                        {loadingProfile ? 'Guardando...' : 'Guardar Cambios'}
                                    </button>
                                </form>
                            </div>
                        )}

                        {/* SECURITY TAB */}
                        {activeTab === 'security' && (
                            <div className="bg-white rounded-xl shadow-md p-8 animate-fade-in">
                                <h1 className="text-2xl font-bold text-azul-marino font-title mb-6 border-b pb-4">Cambiar Contraseña</h1>
                                {passwordMessage && (
                                    <div className={`p-4 rounded mb-6 ${passwordMessage.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                        {passwordMessage.text}
                                    </div>
                                )}
                                <form onSubmit={handleChangePassword} className="space-y-6 max-w-lg">
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 mb-1">Contraseña Actual</label>
                                        <input type="password" value={currentPassword} onChange={e => setCurrentPassword(e.target.value)} className="w-full border rounded px-3 py-2 focus:ring-rosa-principal" required />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 mb-1">Nueva Contraseña</label>
                                        <input type="password" value={newPassword} onChange={e => setNewPassword(e.target.value)} className="w-full border rounded px-3 py-2 focus:ring-rosa-principal" required />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-bold text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                                        <input type="password" value={confirmPassword} onChange={e => setConfirmPassword(e.target.value)} className="w-full border rounded px-3 py-2 focus:ring-rosa-principal" required />
                                    </div>
                                    <button 
                                        type="submit" 
                                        disabled={loadingPassword}
                                        className="bg-rosa-principal text-white px-8 py-3 rounded-full font-bold hover:bg-opacity-90 transition disabled:bg-gray-400 font-button"
                                    >
                                        {loadingPassword ? 'Actualizando...' : 'Actualizar Contraseña'}
                                    </button>
                                </form>
                            </div>
                        )}

                        {/* HISTORY TAB */}
                        {activeTab === 'history' && (
                            <div className="bg-white rounded-xl shadow-md p-8 animate-fade-in">
                                <h1 className="text-2xl font-bold text-azul-marino font-title mb-6 border-b pb-4">Historial de Donaciones</h1>
                                
                                {loadingHistory ? (
                                    <div className="text-center py-10 text-gray-500">Cargando historial...</div>
                                ) : (!donations || donations.length === 0) ? (
                                    <div className="text-center py-12">
                                        <div className="text-6xl mb-4">💙</div>
                                        <h3 className="text-xl font-bold text-gray-700 mb-2">Aún no tienes donaciones</h3>
                                        <p className="text-gray-500 mb-6">Tu ayuda puede cambiar vidas. ¡Empieza hoy!</p>
                                        <button 
                                            onClick={() => setIsDonationModalOpen(true)}
                                            className="px-6 py-3 bg-verde-lima text-white rounded-full font-bold hover:bg-verde-lima-claro transition shadow-lg"
                                        >
                                            Realizar Donación
                                        </button>
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-left border-collapse">
                                            <thead>
                                                <tr className="text-sm text-gray-500 border-b bg-gray-50">
                                                    <th className="py-3 px-4 rounded-tl-lg">Fecha</th>
                                                    <th className="py-3 px-4">Monto</th>
                                                    <th className="py-3 px-4 rounded-tr-lg text-right">Certificado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {Array.isArray(donations) && donations.map((d) => (
                                                    <tr key={d.id} className="border-b last:border-0 hover:bg-gray-50 transition">
                                                        <td className="py-4 px-4 font-sans text-gray-700 capitalize">
                                                            {new Date(d.date).toLocaleDateString('es-ES', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })}
                                                        </td>
                                                        <td className="py-4 px-4 font-bold text-azul-marino">
                                                            Bs {d.amount}
                                                        </td>
                                                        <td className="py-4 px-4 text-right">
                                                            {d.certificate_url ? (
                                                                <a 
                                                                    href={d.certificate_url} 
                                                                    className="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 hover:text-rosa-principal hover:border-rosa-principal px-4 py-2 rounded-lg text-sm font-bold transition shadow-sm"
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                    </svg>
                                                                    Descargar
                                                                </a>
                                                            ) : (
                                                                d.status === 'paid' ? (
                                                                    <span className="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-medium">
                                                                        Generando...
                                                                    </span>
                                                                ) : (
                                                                    <span className="text-gray-400 text-xs italic">No disponible</span>
                                                                )
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>
                        )}

                    </div>
                </div>
            </div>

             <Footer onOpenDonationModal={() => setIsDonationModalOpen(true)} />
             <DonationModal 
                isOpen={isDonationModalOpen} 
                onClose={() => setIsDonationModalOpen(false)} 
            />
        </main>
    );
}