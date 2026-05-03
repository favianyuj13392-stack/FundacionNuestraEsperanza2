"use client";
import React, { useState } from 'react';
import { motion, Variants } from 'framer-motion';

const Contact = () => {
  const [formData, setFormData] = useState({
    name: '',
    last_name: '',
    email: '',
    message: ''
  });
  const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');

  const API_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000/api';

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.id]: e.target.value });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatus('loading');

    try {
      const response = await fetch(`${API_URL}/contact`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        setStatus('success');
        setFormData({ name: '', last_name: '', email: '', message: '' });
      } else {
        setStatus('error');
      }
    } catch (error) {
      console.error(error);
      setStatus('error');
    }
  };

  // Variantes de animación (Tus originales)
  const fieldVariants: Variants = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.5 } }
  };

  return (
    <section id="contacto" className="bg-verde-lima-claro py-16">
      <div className="container mx-auto px-6 text-center">
        <h2 className="text-3xl md:text-4xl font-bold text-black mb-4 font-title">CONTACTO</h2>
        <div className="flex justify-center mb-8"><div className="bg-rosa-principal w-20 h-2"></div></div>
        
        <motion.form 
            onSubmit={handleSubmit}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            className="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg"
        >
          {status === 'success' ? (
              <div className="text-green-600 py-10">
                  <h3 className="text-2xl font-bold">¡Mensaje Enviado!</h3>
                  <p>Nos pondremos en contacto pronto.</p>
                  <button onClick={() => setStatus('idle')} className="mt-4 text-sm underline">Enviar otro</button>
              </div>
          ) : (
            <>
              <div className="grid md:grid-cols-2 gap-4">
                  <motion.div variants={fieldVariants} className="mb-4 text-left">
                    <label htmlFor="name" className='font-bold text-sm'>Nombre</label>
                    <input id="name" type='text' value={formData.name} onChange={handleChange} required className='w-full p-3 rounded border bg-gray-50' />
                  </motion.div>
                  <motion.div variants={fieldVariants} className="mb-4 text-left">
                    <label htmlFor="last_name" className='font-bold text-sm'>Apellido</label>
                    <input id="last_name" type='text' value={formData.last_name} onChange={handleChange} className='w-full p-3 rounded border bg-gray-50' />
                  </motion.div>
              </div>

              <motion.div variants={fieldVariants} className="mb-4 text-left">
                <label htmlFor="email" className='font-bold text-sm'>Correo</label>
                <input id="email" type="email" value={formData.email} onChange={handleChange} required className="w-full p-3 rounded border bg-gray-50" />
              </motion.div>
              
              <motion.div variants={fieldVariants} className="mb-4 text-left">
                <label htmlFor="message" className='font-bold text-sm'>Mensaje</label>
                <textarea id="message" rows={4} value={formData.message} onChange={handleChange} required className="w-full p-3 rounded border bg-gray-50"></textarea>
              </motion.div>

              <button 
                disabled={status === 'loading'}
                className="bg-turquesa-secundario text-white px-8 py-3 rounded-full font-bold w-full hover:bg-blue-600 transition disabled:opacity-50"
              >
                {status === 'loading' ? 'ENVIANDO...' : 'ENVIAR MENSAJE'}
              </button>
              
              {status === 'error' && <p className="text-red-500 mt-2">Error al enviar. Intenta de nuevo.</p>}
            </>
          )}
        </motion.form>
      </div>
    </section>
  );
};

export default Contact;