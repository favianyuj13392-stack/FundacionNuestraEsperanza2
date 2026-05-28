"use client";
import React, { useState } from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import DonationModal from "@/components/DonationModal";
import Alliances from "@/components/Alliances";
import HowToHelp from '@/components/HowToHelp';
import { usePageSection } from '@/hooks/usePageSection';
import HelpHero from '@/components/HelpHero';
import DirectDonations from '@/components/DirectDonations';
import MoreWaysToHelp from '@/components/MoreWaysToHelp';
import SocialMediaHelp from '@/components/SocialMediaHelp';
import QRDonationSection from '@/components/QRDonationSection';

const HelpPage = () => {
  const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);
  const { section, loading } = usePageSection('how_to_help');

  const handleOpenModal = () => setIsDonationModalOpen(true);

  if (!loading && section?.is_active === false) {
    return (
      <main>
        <Navbar onOpenDonationModal={handleOpenModal} />
        <section className="bg-white min-h-[70vh] flex items-center justify-center py-20">
          <div className="text-center px-6">
            <h1 className="text-3xl md:text-4xl font-bold text-azul-marino mb-4">Sección de Cómo Ayudar deshabilitada</h1>
            <p className="text-gray-600">La sección está oculta desde el administrador.</p>
          </div>
        </section>
        <Footer onOpenDonationModal={handleOpenModal} />
      </main>
    );
  }

  const heroTitle = section?.title ?? 'TU AYUDA HACE LA DIFERENCIA';
  const heroSubtitle = section?.subtitle ?? 'Hay muchas formas de ser parte del cambio. Encuentra la tuya.';
  const heroImage = section?.image ?? '/IMG/help-hero-bg.jpg';

  return (
      <main className="bg-white">
        <Navbar onOpenDonationModal={handleOpenModal} />

        <HelpHero 
          title={heroTitle} 
          subtitle={heroSubtitle} 
          image={heroImage} 
        />

        <DirectDonations onOpenDonationModal={handleOpenModal} />
        
        <HowToHelp onOpenDonationModal={handleOpenModal} />
        <MoreWaysToHelp />
        <SocialMediaHelp />
        <QRDonationSection />
        <Alliances />
        
        <Footer onOpenDonationModal={handleOpenModal} />

        <DonationModal 
            isOpen={isDonationModalOpen} 
            onClose={() => setIsDonationModalOpen(false)} 
        />
      </main>
  );
};

export default HelpPage;