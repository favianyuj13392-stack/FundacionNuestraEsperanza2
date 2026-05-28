"use client";
import React from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import DonationModal from "@/components/DonationModal";
import Alliances from "@/components/Alliances";
import HowToHelp from '@/components/HowToHelp';
import { useHomeSectionsContext } from '@/context/HomeSectionsContext';
import HelpHero from '@/components/HelpHero';
import DirectDonations from '@/components/DirectDonations';
import MoreWaysToHelp from '@/components/MoreWaysToHelp';
import SocialMediaHelp from '@/components/SocialMediaHelp';
import QRDonationSection from '@/components/QRDonationSection';
import GenericSection from '@/components/GenericSection';
import SectionGuard from '@/components/SectionGuard';
import { usePageSections, PageSectionData } from '@/hooks/usePageSections';

const HelpPage = () => {
  const [isDonationModalOpen, setIsDonationModalOpen] = React.useState(false);
  const { sections, loading: sectionsLoading, error } = usePageSections();
  const { isSectionActive, loading: statusesLoading } = useHomeSectionsContext();

  const handleOpenModal = () => setIsDonationModalOpen(true);
  const isPageActive = !statusesLoading && isSectionActive('how_to_help');
  const isLoading = sectionsLoading || statusesLoading;

  const renderSection = (section: PageSectionData) => {
    switch (section.identifier) {
      case 'help_hero':
        return (
          <HelpHero
            key={section.id}
            title={section.title ?? 'TU AYUDA HACE LA DIFERENCIA'}
            subtitle={section.subtitle ?? 'Hay muchas formas de ser parte del cambio. Encuentra la tuya.'}
            image={section.image ?? '/IMG/help-hero-bg.jpg'}
          />
        );
      case 'direct_donations':
        return <DirectDonations key={section.id} onOpenDonationModal={handleOpenModal} />;
      case 'how_to_help':
        return <HowToHelp key={section.id} onOpenDonationModal={handleOpenModal} />;
      case 'more_ways_to_help':
        return <MoreWaysToHelp key={section.id} />;
      case 'social_media_help':
        return <SocialMediaHelp key={section.id} />;
      case 'qr_donation_section':
        return <QRDonationSection key={section.id} />;
      default:
        return <GenericSection key={section.id} data={section} />;
    }
  };

  return (
    <main className="bg-white">
      <Navbar onOpenDonationModal={handleOpenModal} />

      {isLoading ? (
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
        </div>
      ) : !isPageActive ? (
        <section className="bg-white min-h-[70vh] flex items-center justify-center py-20">
          <div className="text-center px-6">
            <h1 className="text-3xl md:text-4xl font-bold text-azul-marino mb-4">Sección de Cómo Ayudar deshabilitada</h1>
            <p className="text-gray-600">La sección está oculta desde el administrador.</p>
          </div>
        </section>
      ) : sections.length === 0 ? (
        <section className="bg-white min-h-[70vh] flex items-center justify-center py-20">
          <div className="text-center px-6">
            <h1 className="text-3xl md:text-4xl font-bold text-azul-marino mb-4">No hay secciones disponibles</h1>
            <p className="text-gray-600">No se encontró contenido activo para esta página.</p>
            {error && <p className="text-red-600 mt-4">{error}</p>}
          </div>
        </section>
      ) : (
        sections.map((section) => renderSection(section))
      )}

      <SectionGuard identifier="alliances">
        <Alliances />
      </SectionGuard>

      <Footer onOpenDonationModal={handleOpenModal} />

      <DonationModal
        isOpen={isDonationModalOpen}
        onClose={() => setIsDonationModalOpen(false)}
      />
    </main>
  );
};

export default HelpPage;