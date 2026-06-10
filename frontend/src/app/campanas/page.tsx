"use client";
import React, { useEffect, useState } from 'react';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import Image from 'next/image';
import Link from 'next/link';
import { motion } from 'framer-motion';
import { fetchTransparencyCampaigns, TransparencyCampaign } from '@/services/transparencyService';

import DonationModal from '@/components/DonationModal';

export default function CampanasPage() {
    const [campaigns, setCampaigns] = useState<TransparencyCampaign[]>([]);
    const [loading, setLoading] = useState(true);
    const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);
    const [selectedCampaignId, setSelectedCampaignId] = useState<number | undefined>(undefined);

    useEffect(() => {
        const fetchCampaigns = async () => {
            try {
                const data = await fetchTransparencyCampaigns();
                setCampaigns(data);
            } catch (error) {
                console.error("Error fetching campaigns", error);
            } finally {
                setLoading(false);
            }
        };

        fetchCampaigns();
    }, []);

    return (
        <main className="bg-white min-h-screen flex flex-col">
            <Navbar onOpenDonationModal={() => setIsDonationModalOpen(true)} />

            {/* Hero Section */}
            <section className="bg-rosa-principal text-white py-20 px-6 text-center mt-16">
                <motion.h1 
                    initial={{ opacity: 0, y: -20 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="text-4xl md:text-5xl font-bold font-title mb-4"
                >
                    Nuestras Campañas
                </motion.h1>
                <motion.p 
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.2 }}
                    className="text-lg md:text-xl font-sans max-w-2xl mx-auto"
                >
                    Conoce los proyectos activos en los que estamos trabajando y únete a nuestra causa. Transparencia y dedicación en cada paso.
                </motion.p>
            </section>

            {/* Grid de Campañas */}
            <section className="flex-grow container mx-auto py-16 px-6">
                {loading ? (
                    <div className="flex justify-center items-center py-20">
                        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
                    </div>
                ) : campaigns.length === 0 ? (
                    <div className="text-center py-20 text-gray-500 font-sans">
                        <p className="text-xl">En este momento no hay campañas activas específicas.</p>
                        <p className="mt-4">Sin embargo, siempre puedes apoyar a nuestro <Link href="/como-ayudar" className="text-rosa-principal font-bold hover:underline">Fondo General</Link>.</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {campaigns.map((campaign) => (
                            <motion.div 
                                key={campaign.id}
                                initial={{ opacity: 0, scale: 0.95 }}
                                animate={{ opacity: 1, scale: 1 }}
                                whileHover={{ y: -5, boxShadow: "0px 10px 20px rgba(0,0,0,0.1)" }}
                                className="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col border border-gray-100"
                            >
                                {/* Imagen */}
                                <div className="relative h-48 w-full bg-gray-200">
                                    {campaign.image_path ? (
                                        <Image 
                                            src={campaign.image_path.startsWith('http') ? campaign.image_path : `${process.env.NEXT_PUBLIC_API_BASE_URL ? process.env.NEXT_PUBLIC_API_BASE_URL.replace('/api', '') : 'http://127.0.0.1:8000'}/storage/${campaign.image_path}`} 
                                            alt={campaign.name} 
                                            fill 
                                            className="object-cover" 
                                        />
                                    ) : (
                                        <div className="flex items-center justify-center h-full text-gray-400">Sin Imagen</div>
                                    )}
                                    <div className="absolute top-4 right-4 bg-white/90 px-3 py-1 rounded-full text-xs font-bold text-azul-marino shadow-sm backdrop-blur-sm">
                                        Activa
                                    </div>
                                </div>

                                {/* Contenido */}
                                <div className="p-6 flex-grow flex flex-col">
                                    <h2 className="text-2xl font-bold font-title text-azul-marino mb-2">{campaign.name}</h2>
                                    <p className="text-gray-600 font-sans text-sm mb-6 line-clamp-3 flex-grow">
                                        {campaign.description || 'Apoya esta campaña y ayúdanos a lograr nuestra meta.'}
                                    </p>

                                    {/* Barra de Progreso (Transparencia) */}
                                    <div className="mb-6">
                                        <div className="flex justify-between text-sm font-bold text-gray-700 mb-1 font-sans">
                                            <span>Recaudado: {campaign.total_recaudado} Bs</span>
                                            <span className="text-rosa-principal">Meta: {campaign.monetary_goal} Bs</span>
                                        </div>
                                        <div className="w-full bg-gray-200 rounded-full h-2.5">
                                            <div 
                                                className="bg-turquesa-secundario h-2.5 rounded-full transition-all duration-1000" 
                                                style={{ width: `${campaign.progress_percentage}%` }}
                                            ></div>
                                        </div>
                                        <div className="text-right text-xs text-gray-500 mt-1 font-bold">{campaign.progress_percentage}% alcanzado</div>
                                    </div>

                                    {/* Botón de Acción */}
                                    <div className="flex flex-col gap-3 mt-4">
                                        <button 
                                            onClick={() => {
                                                setSelectedCampaignId(campaign.id);
                                                setIsDonationModalOpen(true);
                                            }}
                                            className="w-full text-center bg-rosa-principal text-white py-3 rounded-full font-bold hover:bg-opacity-90 transition font-button"
                                        >
                                            DONAR A ESTA CAMPAÑA
                                        </button>
                                        <Link 
                                            href={`/transparencia/${campaign.slug}`}
                                            className="w-full text-center bg-turquesa-secundario text-white py-3 rounded-full font-bold hover:bg-opacity-90 transition font-button"
                                        >
                                            VER TRAZABILIDAD
                                        </Link>
                                    </div>
                                </div>
                            </motion.div>
                        ))}
                    </div>
                )}
            </section>

            <Footer onOpenDonationModal={() => setIsDonationModalOpen(true)} />
            
            <DonationModal 
                isOpen={isDonationModalOpen} 
                onClose={() => {
                    setIsDonationModalOpen(false);
                    setSelectedCampaignId(undefined);
                }} 
                campaignId={selectedCampaignId}
            />
        </main>
    );
}
