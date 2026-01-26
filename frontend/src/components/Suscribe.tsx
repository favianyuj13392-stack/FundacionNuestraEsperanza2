"use client";
import React, { useState } from 'react';

// 1. DEFINICIÓN SEGURA DE LA URL
const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000';

const Suscribe = () => {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
  const [message, setMessage] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus('loading');

    try {
      console.log("Intentando suscribir a:", `${API_URL}/api/subscribe`); // Log para depurar

      // 2. PETICIÓN FETCH CORREGIDA
      // Nota: Verificamos si usamos /subscribe o /subscribers según tu ruta de Laravel
      const response = await fetch(`${API_URL}/api/subscribe`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json' // Importante para Laravel
        },
        body: JSON.stringify({ email }),
      });

      const data = await response.json();

      if (response.ok) {
        setStatus('success');
        setMessage('¡Gracias por suscribirte!');
        setEmail(''); // Limpiamos el campo
      } else {
        setStatus('error');
        // Si Laravel devuelve errores de validación, los mostramos
        setMessage(data.message || 'Hubo un error al suscribirse.');
      }
    } catch (error) {
      console.error("Error de conexión:", error);
      setStatus('error');
      setMessage('Error de conexión con el servidor.');
    }
  };

  return (
    <section className="bg-beige-claro py-16 text-black">
      <div className="container mx-auto px-6 text-center">
        
        <h2 className="text-3xl font-bold mb-4 font-title">Suscríbete</h2>
        <p className="mb-8 font-sans">
          Recibe las últimas noticias y actualizaciones de nuestra fundación.
        </p>

        <form onSubmit={handleSubmit} className="max-w-lg mx-auto flex flex-col sm:flex-row gap-4">
          <input
            type="email"
            placeholder="Tu correo electrónico"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            className="flex-grow px-4 py-3 rounded-full text-gray-800 focus:outline-none focus:ring-2 focus:ring-rosa-principal"
          />
          <button 
            type="submit" 
            disabled={status === 'loading'}
            className="bg-rosa-principal px-8 py-3 rounded-full font-bold hover:bg-amarillo-detalle transition duration-300 font-button disabled:opacity-50  text-white"
          >
            {status === 'loading' ? 'ENVIANDO...' : 'SUSCRIBIRME'}
          </button>
        </form>

        {/* Mensajes de Feedback */}
        {status === 'success' && (
          <p className="mt-4 text-green-400 font-bold animate-pulse">
            {message}
          </p>
        )}
        {status === 'error' && (
          <p className="mt-4 text-red-400 font-bold">
            {message}
          </p>
        )}

      </div>
    </section>
  );
};

export default Suscribe;