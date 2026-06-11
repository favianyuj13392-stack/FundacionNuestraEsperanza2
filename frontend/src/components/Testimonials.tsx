"use client";
import React, { useState, useEffect } from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';
import { resolveImageUrl } from '@/utils/imageUrl';

// --- 1. CONFIGURACIÓN DE URL DEL BACKEND (Igual que en Programs) ---
// Define aquí la dirección de tu backend Laravel.
const LARAVEL_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000'; 
const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000/api'; 

// Interfaz alineada con tu API y BD
interface TestimonialItem {
  id: number;
  name: string;
  role: string | null;
  message: string;
  image: string | null;
}

// Interfaz para la respuesta cruda de la API (opcional, pero buena práctica)
interface ApiTestimonialItem {
  id: number;
  name: string;
  role?: string | null;
  message?: string;
  content?: string; // Por si el backend manda 'content' en vez de 'message'
  image?: string | null;
  quote?: string;   // Por si el backend manda 'quote'
}

const TestimonialsSection = () => {
  const [testimonials, setTestimonials] = useState<TestimonialItem[]>([]);
  const [loading, setLoading] = useState(true);

  // Efecto para cargar los datos del API
  useEffect(() => {
    const fetchTestimonials = async () => {
      try {
        // Usamos la constante o una variable de entorno para la API
        const response = await fetch(`${API_BASE_URL}/testimonials`);
        if (!response.ok) {
            throw new Error(`Error en la respuesta de la API: ${response.status}`);
        }
        const data = await response.json();
        
        // Mapeamos los datos para asegurar que coincidan con nuestra interfaz
        // Tomamos los primeros 3 para la Home
        const normalizedData = data.slice(0, 3).map((item: ApiTestimonialItem) => ({
          id: item.id,
          name: item.name,
          role: item.role || 'Beneficiario',
          // Buscamos el contenido en varios campos posibles para ser robustos
          message: item.message || item.quote || item.content || 'Sin testimonio disponible.',
          image: item.image 
        }));
        
        setTestimonials(normalizedData);
        setLoading(false);
        
      } catch (error) {
        console.error("Fallo al cargar testimonios desde la API:", error);
        setLoading(false); 
      }
    };
    fetchTestimonials();
  }, []);

  const cardVariants = {
    hidden: { opacity: 0, y: 50 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.6 } }
  };

  // --- MANEJO DE CARGA Y ERROR ---
  if (loading) {
    return (
      <section className="py-16 bg-white">
        <div className="container mx-auto px-6 text-center">
            <h2 className="text-3xl font-bold text-black mb-10 font-title">TESTIMONIOS</h2>
            <div className="flex justify-center mt-5">
                <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-rosa-principal"></div>
            </div>
            <p className="mt-4 text-gray-600">Cargando testimonios...</p>
        </div>
      </section>
    );
  }

  if (testimonials.length === 0) {
    // Si no hay datos, no mostramos la sección o mostramos un mensaje vacío
    // Para la Home, a veces es mejor ocultar la sección si falla.
    // Aquí mostramos un mensaje de error para depuración.
    return (
      <section className="py-16 bg-white">
        <div className="container mx-auto px-6 text-center">
          <h2 className="text-3xl font-bold text-black mb-10 font-title">TESTIMONIOS</h2>
          <p className="text-red-500">No hay testimonios disponibles en este momento.</p>
        </div>
      </section>
    );
  }

  // --- RENDERIZADO ---
  return (
    <section className="relative py-16 md:py-20 bg-gradient-to-br from-celeste-claro to-white overflow-hidden">
      <div className="absolute -left-16 top-16 h-48 w-48 rounded-full bg-rosa-principal/20 blur-3xl"></div>
      <div className="absolute right-0 top-24 h-64 w-64 rounded-full bg-turquesa-secundario/15 blur-3xl"></div>
      <div className="container mx-auto px-6">
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold text-black mb-2 font-title"> TESTIMONIOS </h2>
          <div className="flex justify-center">
            <div className="bg-rosa-principal w-20 h-2"></div>
          </div>
        </div>
        
        <motion.div 
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true, amount: 0.2 }}
        >
          {testimonials.map((testimonial) => (
            <motion.div 
              key={testimonial.id} 
              className="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col items-center p-6 border-t-4 border-turquesa-secundario transition-shadow duration-300 hover:shadow-xl"
              variants={cardVariants}
            >
              <div className="relative w-28 h-28 rounded-full overflow-hidden mb-4 border-4 border-rosa-principal">
                
                {/* --- 3. APLICACIÓN DE LA SOLUCIÓN DE IMAGEN --- */}
                <Image
                  // Usamos la función helper aquí
                  src={resolveImageUrl(testimonial.image)}
                  alt={`Foto de ${testimonial.name}`}
                  layout="fill"
                  objectFit="cover"
                  // IMPORTANTE: Esto permite cargar imágenes de localhost sin configuración extra
                  unoptimized={true} 
                />
                
              </div>

              <div className="text-center flex flex-col flex-grow w-full">
                <h4 className="text-xl font-bold text-black mb-1 font-title">
                    {testimonial.name}
                </h4>
                
                <p className="text-sm text-turquesa-secundario font-bold font-sans mb-4 uppercase">
                    {testimonial.role}
                </p>

                <div className="text-gray-700 font-sans text-sm italic mb-4 flex-grow line-clamp-4">
                    {/* Renderizamos el mensaje de forma segura. 
                        Si viene HTML del editor de texto enriquecido, usa dangerouslySetInnerHTML con cuidado.
                        Si es texto plano, usa <p>{testimonial.message}</p> */}
                    <div dangerouslySetInnerHTML={{ __html: testimonial.message }} />
                </div>

              </div>
            </motion.div>
          ))}
        </motion.div>

        <div className="text-center mt-12">
          <a href="/testimonials" className="bg-rosa-principal text-white px-8 py-3 rounded-full font-bold hover:bg-amarillo-detalle transition duration-300 font-button inline-block">
            VER TODAS LAS HISTORIAS
          </a>
        </div>
      </div>
    </section>
  );
};

export default TestimonialsSection;