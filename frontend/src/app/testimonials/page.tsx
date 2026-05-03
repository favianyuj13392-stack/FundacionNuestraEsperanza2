"use client";
import React, { useState, useEffect } from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import Alliances from '@/components/Alliances';
import DonationModal from '@/components/DonationModal'; 
import Quotes from '@/components/Quotes';

interface TestimonialItem {
    id: number;
    name: string;
    role: string | null;
    message: string;
    image: string | null;
}
// 2. Número de la fundación 
  const phoneNumber = "59170112236"; 
  const whatsappUrl = `https://wa.me/${phoneNumber}?text=Hola,%20quisiera%20ser%20voluntario%20de%20la%20fundación.`;

export default function TestimonialsPage() {
    const [testimonials, setTestimonials] = useState<TestimonialItem[]>([]);
    const [loading, setLoading] = useState(true);

    // 2. ESTADO DEL MODAL
    const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);

    const API_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000/api';

    useEffect(() => {
        interface ApiTestimonial {
            id: number;
            name: string;
            role?: string | null;
            message?: string | null;
            content?: string | null;
            image?: string | null;
        }

        const loadTestimonials = async () => {
            try {
                const response = await fetch(`${API_URL}/testimonials`);
                if (response.ok) {
                    const data: ApiTestimonial[] = await response.json();
                    
                    const normalizedData: TestimonialItem[] = data.map((item) => ({
                        id: item.id,
                        name: item.name,
                        role: item.role ?? 'Beneficiario',
                        message: item.message ?? item.content ?? '',
                        image: item.image ?? null
                    }));

                    setTestimonials(normalizedData);
                } else {
                    console.error("Error al obtener testimonios");
                }
            } catch (error) {
                console.error("Error de conexión:", error);
            } finally {
                setLoading(false);
            }
        };

        loadTestimonials();
    }, []);

    return (
        <main>
            {/* 3. CONECTAR NAVBAR */}
            <Navbar onOpenDonationModal={() => setIsDonationModalOpen(true)} />
            
            <section className="bg-celeste-claro py-20 text-center">
                <div className="container mx-auto px-6">
                    <h1 className="text-4xl md:text-5xl font-bold text-azul-marino font-title mb-6">
                        Testimonios
                    </h1>
                    <p className="text-xl text-gray-700 max-w-3xl mx-auto font-sans">
                        Historias reales de esperanza, lucha y superación que nos inspiran cada día.
                    </p>
                </div>
            </section>

            <section className="bg-white py-16">
                <div className="container mx-auto px-6">
                    
                    {loading ? (
                        <div className="text-center py-20 text-gray-500">Cargando historias...</div>
                    ) : testimonials.length === 0 ? (
                        <div className="text-center py-20 text-gray-500">Aún no hay testimonios registrados.</div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                            {testimonials.map((item, index) => (
                                <motion.div 
                                    key={item.id} 
                                    initial={{ opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ duration: 0.5, delay: index * 0.1 }}
                                    whileHover={{ y: -10, boxShadow: "0px 20px 30px rgba(0,0,0,0.15)" }}
                                    className="bg-beige-claro rounded-xl shadow-lg overflow-hidden flex flex-col h-full hover:shadow-2xl transition-all duration-300"
                                >
                                    <div className="relative w-full h-64 bg-gray-200">
                                        {item.image ? (
                                            <Image 
                                                src={item.image} 
                                                alt={item.name} 
                                                fill
                                                className="object-cover"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center bg-gray-300 text-gray-500 text-6xl font-title font-bold">
                                                {item.name.charAt(0)}
                                            </div>
                                        )}
                                    </div>

                                    <div className="p-8 flex flex-col flex-grow text-center relative">
                                        <div className="text-rosa-principal text-6xl font-serif leading-none absolute -top-6 left-1/2 transform -translate-x-1/2 bg-beige-claro px-2 rounded-full">
                                            “
                                        </div>

                                        <h3 className="text-2xl font-bold text-azul-marino font-title mt-4 mb-2">
                                            {item.name}
                                        </h3>
                                        
                                        <p className="text-turquesa-secundario font-bold text-sm uppercase mb-4 tracking-wide">
                                            {item.role}
                                        </p>

                                        <div 
                                            className="text-gray-700 font-sans italic mb-6 flex-grow leading-relaxed"
                                            dangerouslySetInnerHTML={{ __html: item.message }}
                                        />
                                    </div>
                                </motion.div>
                            ))}
                        </div>
                    )}

                    <div className="mt-20 bg-azul-marino rounded-2xl p-10 md:p-16 text-white text-center">
                        <h3 className="text-2xl md:text-3xl font-bold font-title mb-4">¿Tienes una historia que contar?</h3>
                        <p className="mb-8 font-sans max-w-2xl mx-auto">
                            Si has sido parte de nuestra fundación, nos encantaría conocer tu experiencia.
                        </p>
                        <a 
                            href={whatsappUrl}
                            target="_blank"
                            rel="noopener noreferrer" 
                            className="inline-block bg-white text-azul-marino px-6 py-2 rounded-full font-bold hover:bg-amarillo-detalle transition duration-300 font-button"
                            >
                            CONTÁCTANOS
                            </a>     
                    </div>

                </div>
            </section>
            <Quotes/>
            <Alliances />
            
            {/* 4. CONECTAR FOOTER */}
            <Footer onOpenDonationModal={() => setIsDonationModalOpen(true)} />

            {/* 5. RENDERIZAR MODAL */}
            <DonationModal 
                isOpen={isDonationModalOpen} 
                onClose={() => setIsDonationModalOpen(false)} 
            />
        </main>
    );
};