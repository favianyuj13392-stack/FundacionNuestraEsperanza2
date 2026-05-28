"use client";
import React, { useState } from 'react'; 
// Componentes
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer"; 
import Quotes from '@/components/Quotes';
import Suscribe from '@/components/Suscribe'; 
import AboutUs from '@/components/AboutUs';
import DonationModal from '@/components/DonationModal'; 
import MissionVision from '@/components/MissionVision';
import OurPrograms from '@/components/OurPrograms';
import { usePageSection } from '@/hooks/usePageSection';
import AboutHero from '@/components/AboutHero';
import TeamDirectory from '@/components/TeamDirectory';

export default function AboutPage() {
  const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);
  const { section, loading } = usePageSection('about_us');

  const handleOpenModal = () => setIsDonationModalOpen(true);

  if (!loading && section?.is_active === false) {
    return (
      <main>
        <Navbar onOpenDonationModal={handleOpenModal} />
        <section className="bg-white min-h-[70vh] flex items-center justify-center py-20">
          <div className="text-center px-6">
            <h1 className="text-3xl md:text-4xl font-bold text-azul-marino mb-4">Sección de Quiénes Somos deshabilitada</h1>
            <p className="text-gray-600">La sección está oculta desde el administrador.</p>
          </div>
        </section>
        <Footer onOpenDonationModal={handleOpenModal} />
      </main>
    );
  }

  const heroTitle = section?.title ?? 'Quiénes Somos';
  const heroSubtitle = section?.subtitle ?? 'Somos una fundación comprometida con brindar apoyo integral a los niños, niñas y adolescentes en situación de cáncer.';
  const heroImage = section?.image ?? '/IMG/Equipo.jpg';

  return (
      <main className="overflow-x-hidden">
        <Navbar onOpenDonationModal={handleOpenModal} />
        
        <AboutHero 
          title={heroTitle} 
          subtitle={heroSubtitle} 
          image={heroImage} 
        />
        
        <AboutUs />
        <MissionVision />
        <OurPrograms />
        
        <TeamDirectory />

        <Quotes />
        <Suscribe />
        
        <Footer onOpenDonationModal={handleOpenModal} />
        
        <DonationModal 
          isOpen={isDonationModalOpen} 
          onClose={() => setIsDonationModalOpen(false)} 
        />
      </main>
  );
};