"use client";
import React, { useState } from 'react';
import { useAuth } from '@/context/AuthContext';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { API_BASE_URL } from '@/utils/apiBaseUrl';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const { login } = useAuth();
  const router = useRouter();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError(null);

    const API_URL = API_BASE_URL;

    try {
      // Login directo con tokens bearer (sin CSRF cookies)
      const response = await fetch(`${API_URL}/api/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();

      if (!response.ok) {
        // Error de Laravel (ej. credenciales incorrectas)
        setError(data.message || 'Error al iniciar sesión.');
        setIsLoading(false);
        return;
      }

      // --- ¡ÉXITO! ---
      // La respuesta del backend es: { message: "...", data: { user: {...}, token: "..." } }
      const responseData = data.data;

      if (responseData && responseData.user && responseData.token) {
        login(responseData.user, responseData.token); // Usamos la función del AuthContext
        
        // Check if there's a pending donation intent
        if (localStorage.getItem('pendingDonation')) {
            router.push('/como-ayudar');
        } else {
            // Check for redirect param
            const urlParams = new URLSearchParams(window.location.search);
            const redirect = urlParams.get('redirect');
            
            if (redirect === 'back') {
                 router.back();
            } else {
              router.push('/perfil'); // Redirigir al perfil del usuario
            }
        }
      } else {
        setError('Respuesta del servidor inválida.');
      }
    } catch (error) {
      console.error('Login error:', error);
      setError('Error de conexión. Inténtalo de nuevo.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-beige-claro px-4">
      <div className="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-xl relative">
        
        {/* --- BOTÓN DE VOLVER --- */}
        <div className="absolute top-4 left-4">
            <Link 
                href="/" 
                className="text-gray-500 hover:text-rosa-principal text-sm font-bold flex items-center transition-colors"
            >
                ← Volver al Inicio
            </Link>
        </div>
        {/* ----------------------- */}

        <div className="flex justify-center mb-6 mt-4">
          <div className="relative w-24 h-24">
             <Image 
               src="/IMG/Logo.jpg" 
               alt="Fundación Nuestra Esperanza" 
               fill
               className="object-contain"
             />
          </div>
        </div>

        <h2 className="text-3xl font-bold text-center text-azul-marino font-title">
          Iniciar Sesión
        </h2>
        
        <p className="text-center text-gray-500 font-sans text-sm">
          Ingresa a tu cuenta de donante
        </p>

        <form onSubmit={handleSubmit} className="space-y-6 mt-4">
          <div>
            <label htmlFor="email" className="block mb-2 text-sm font-bold text-gray-700 font-sans">
              Correo Electrónico
            </label>
            <input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              placeholder="ejemplo@correo.com"
              className="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rosa-principal"
            />
          </div>

          <div>
            <label htmlFor="password" className="block mb-2 text-sm font-bold text-gray-700 font-sans">
              Contraseña
            </label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              placeholder="********"
              className="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rosa-principal"
            />
          </div>

          {error && (
            <div className="p-3 rounded-md bg-red-50 border border-red-200">
                <p className="text-sm text-center text-red-600 font-bold">{error}</p>
            </div>
          )}

          <button
            type="submit"
            disabled={isLoading}
            className="w-full px-4 py-3 font-bold text-white transition-colors duration-300 rounded-full bg-rosa-principal hover:bg-amarillo-detalle font-button disabled:bg-gray-400 disabled:cursor-not-allowed shadow-md hover:shadow-lg"
          >
            {isLoading ? (
                <span className="flex items-center justify-center gap-2">
                    <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Validando...
                </span>
            ) : 'INGRESAR'}
          </button>
        </form>

        <div className="text-center pt-4 border-t border-gray-100">
            <p className="text-sm text-gray-600 font-sans">
            ¿No tienes una cuenta?{' '}
            <Link href="/registro" className="font-bold text-turquesa-secundario hover:underline">
                Regístrate aquí
            </Link>
            </p>
        </div>
      </div>
    </div>
  );
}