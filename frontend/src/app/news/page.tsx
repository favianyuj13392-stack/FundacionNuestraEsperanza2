"use client";
import React, { useState, useEffect } from 'react';
import Image from 'next/image';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import Alliances from '@/components/Alliances';
import DonationModal from '@/components/DonationModal'; // <--- 1. IMPORTAR MODAL

// 1. Interfaz exacta de lo que devuelve Laravel
interface NewsItem {
    id: number;
    title: string;
    content: string; 
    image: string | null;
    date: string;
}

const CARDS_PER_PAGE = 6; 

export default function NewsPage() {
    // Estados de Datos
    const [news, setNews] = useState<NewsItem[]>([]);
    const [loading, setLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [expandedId, setExpandedId] = useState<number | null>(null);

    // 2. ESTADO DEL MODAL (Nuevo)
    const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);

    // URL del Backend
    const API_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://127.0.0.1:8000/api';

    // Cargar datos del Backend
    useEffect(() => {
        const loadNews = async () => {
            try {
                const response = await fetch(`${API_URL}/news`);
                if (response.ok) {
                    const data = await response.json();
                    setNews(data);
                } else {
                    console.error("Error al obtener noticias");
                }
            } catch (error) {
                console.error("Error de conexión:", error);
            } finally {
                setLoading(false);
            }
        };

        loadNews();
    }, []);

    // Función para alternar expandir/contraer
    const toggleExpand = (id: number) => {
        setExpandedId(prevId => (prevId === id ? null : id));
    };

    // Lógica de Paginación
    const indexOfLastCard = currentPage * CARDS_PER_PAGE;
    const indexOfFirstCard = indexOfLastCard - CARDS_PER_PAGE;
    const currentNews = news.slice(indexOfFirstCard, indexOfLastCard);
    const totalPages = Math.ceil(news.length / CARDS_PER_PAGE);

    return (
        <main>
            {/* 3. CONECTAR NAVBAR */}
            <Navbar onOpenDonationModal={() => setIsDonationModalOpen(true)} />
            
            {/* Header */}
            <section className="bg-beige-claro py-20 text-center">
                <div className="container mx-auto px-6">
                    <h1 className="text-4xl md:text-5xl font-bold text-azul-marino font-title mb-6">
                        Noticias y Eventos
                    </h1>
                    <p className="text-xl text-gray-700 max-w-3xl mx-auto font-sans">
                        Mantente al día con nuestras actividades y logros.
                    </p>
                </div>
            </section>

            {/* Lista de Noticias */}
            <section className="bg-white py-16">
                <div className="container mx-auto px-6">
                    
                    {loading ? (
                        <div className="text-center py-20 text-gray-500">Cargando noticias...</div>
                    ) : news.length === 0 ? (
                        <div className="text-center py-20 text-gray-500">No hay noticias publicadas aún.</div>
                    ) : (
                        <>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto items-start">
                                {currentNews.map((item) => {
                                    const isExpanded = expandedId === item.id;

                                    return (
                                        <div 
                                            key={item.id} 
                                            className={`bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 flex flex-col ${isExpanded ? 'row-span-2' : ''}`}
                                        >
                                            {/* Imagen */}
                                            <div className="relative h-56 w-full bg-gray-100 flex-shrink-0">
                                                {item.image ? (
                                                    <Image 
                                                        src={item.image} 
                                                        alt={item.title} 
                                                        fill
                                                        className="object-cover"
                                                    />
                                                ) : (
                                                    <div className="flex items-center justify-center h-full text-gray-400">
                                                        Sin Imagen
                                                    </div>
                                                )}
                                                <div className="absolute top-4 right-4 bg-rosa-principal text-white text-xs font-bold px-3 py-1 rounded-full">
                                                    {item.date || 'Reciente'}
                                                </div>
                                            </div>

                                            {/* Contenido */}
                                            <div className="p-6 flex flex-col flex-grow">
                                                <span className="text-turquesa-secundario text-sm font-bold mb-2 uppercase">
                                                    Noticias
                                                </span>
                                                <h3 className="text-xl font-bold font-title mb-3 text-azul-marino">
                                                    {item.title}
                                                </h3>
                                                
                                                <div 
                                                    className={`text-gray-600 font-sans text-sm mb-4 transition-all duration-500 ease-in-out ${isExpanded ? '' : 'line-clamp-3'}`}
                                                    dangerouslySetInnerHTML={{ __html: item.content }}
                                                />

                                                <button 
                                                    onClick={() => toggleExpand(item.id)}
                                                    className="text-rosa-principal font-bold hover:underline self-start mt-auto focus:outline-none"
                                                >
                                                    {isExpanded ? 'Leer menos ↑' : 'Leer más →'}
                                                </button>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            {/* Paginación */}
                            {totalPages > 1 && (
                                <div className="flex justify-center mt-12 space-x-2">
                                    <button
                                        onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
                                        disabled={currentPage === 1}
                                        className={`px-4 py-2 rounded-md ${currentPage === 1 ? 'text-gray-300' : 'text-gray-600 hover:bg-gray-100'}`}
                                    >
                                        Anterior
                                    </button>
                                    
                                    <span className="px-4 py-2 text-rosa-principal font-bold">
                                        Página {currentPage} de {totalPages}
                                    </span>

                                    <button
                                        onClick={() => setCurrentPage((prev) => Math.min(prev + 1, totalPages))}
                                        disabled={currentPage === totalPages}
                                        className={`px-4 py-2 rounded-md ${currentPage === totalPages ? 'text-gray-300' : 'text-gray-600 hover:bg-gray-100'}`}
                                    >
                                        Siguiente
                                    </button>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </section>
            
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