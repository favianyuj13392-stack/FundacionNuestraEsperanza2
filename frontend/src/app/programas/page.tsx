"use client";
import React, { useState } from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import Alliances from '@/components/Alliances';
import Contact from '@/components/Contact';
import Subscribe from '@/components/Suscribe';
import HowToHelp from '@/components/HowToHelp';
import DonationModal from '@/components/DonationModal';
import ProgramModal from '@/components/ProgramModal'; 
import { usePageSection } from '@/hooks/usePageSection';
import ProgramsHero from '@/components/ProgramsHero';
import ProgramsSection, { Program } from '@/components/ProgramsSection';

export default function ProgramsPage() {
    // Estados para los Modales
    const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);
    const [selectedProgram, setSelectedProgram] = useState<Program | null>(null);

    const { section, loading: sectionLoading } = usePageSection('programs');
    
    const handleOpenDonationModal = () => setIsDonationModalOpen(true);

    if (!sectionLoading && section?.is_active === false) {
      return (
        <main>
          <Navbar onOpenDonationModal={handleOpenDonationModal} />
          <section className="bg-white min-h-[70vh] flex items-center justify-center py-20">
            <div className="text-center px-6">
              <h1 className="text-3xl md:text-4xl font-bold text-azul-marino mb-4">Sección de Programas deshabilitada</h1>
              <p className="text-gray-600">La sección de programas está oculta desde el administrador.</p>
            </div>
          </section>
          <Footer onOpenDonationModal={handleOpenDonationModal} />
        </main>
      );
    }

    const headerTitle = section?.title ?? 'Nuestros Programas';
    const headerSubtitle = section?.subtitle ?? 'Proyectos diseñados para brindar esperanza y soporte integral.';

    return (
        <main>
            <Navbar onOpenDonationModal={handleOpenDonationModal} />
            
            <ProgramsHero 
                title={headerTitle} 
                subtitle={headerSubtitle} 
            />

            <ProgramsSection 
                onOpenProgramModal={setSelectedProgram} 
            />

            <HowToHelp onOpenDonationModal={handleOpenDonationModal} />
            <Alliances />
            <Subscribe />
            <Contact />
            <Footer onOpenDonationModal={handleOpenDonationModal} />

            {/* Modales */}
            <DonationModal 
                isOpen={isDonationModalOpen} 
                onClose={() => setIsDonationModalOpen(false)} 
            />

            <ProgramModal 
                isOpen={!!selectedProgram} 
                program={selectedProgram} 
                onClose={() => setSelectedProgram(null)} 
            />
        </main>
    );
}