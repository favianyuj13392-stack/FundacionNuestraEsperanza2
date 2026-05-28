"use client";
import React, { useState } from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import Alliances from '@/components/Alliances';
import DonationModal from '@/components/DonationModal';
import { usePageSection } from '@/hooks/usePageSection';
import NewsHero from '@/components/NewsHero';
import NewsSection from '@/components/NewsSection';

export default function NewsPage() {
    const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);
    const { section, loading: sectionLoading } = usePageSection('news');

    const handleOpenModal = () => setIsDonationModalOpen(true);

    if (!sectionLoading && section?.is_active === false) {
        return (
            <main>
                <Navbar onOpenDonationModal={handleOpenModal} />
                <section className="bg-white min-h-[70vh] flex items-center justify-center py-20">
                    <div className="text-center px-6">
                        <h1 className="text-3xl md:text-4xl font-bold text-azul-marino mb-4">Sección de Noticias deshabilitada</h1>
                        <p className="text-gray-600">La sección está oculta desde el administrador.</p>
                    </div>
                </section>
                <Footer onOpenDonationModal={handleOpenModal} />
            </main>
        );
    }

    const headerTitle = section?.title ?? 'Noticias y Eventos';
    const headerSubtitle = section?.subtitle ?? 'Mantente al día con nuestras actividades y logros.';
    const headerImage = section?.image ?? null;

    return (
        <main>
            <Navbar onOpenDonationModal={handleOpenModal} />
            
            <NewsHero 
                title={headerTitle} 
                subtitle={headerSubtitle} 
                image={headerImage} 
            />

            <NewsSection />
            
            <Alliances />
            
            <Footer onOpenDonationModal={handleOpenModal} />

            <DonationModal 
                isOpen={isDonationModalOpen} 
                onClose={() => setIsDonationModalOpen(false)} 
            />
        </main>
    );
}