"use client";
import React, { useState, useEffect } from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import DonationModal from "@/components/DonationModal";
import Alliances from "@/components/Alliances";
import HowToHelp from '@/components/HowToHelp';
import HelpHero from '@/components/HelpHero';
import DirectDonations from '@/components/DirectDonations';
import MoreWaysToHelp from '@/components/MoreWaysToHelp';
import SocialMediaHelp from '@/components/SocialMediaHelp';
import QRDonationSection from '@/components/QRDonationSection';
import GenericSection from '@/components/GenericSection';
import Advertisements from '@/components/Advertisement';
import { API_BASE_URL } from '@/utils/apiBaseUrl';

interface HowToHelpSectionData {
  id: number;
  identifier: string;
  name: string;
  title?: string;
  subtitle?: string;
  content?: string;
  image?: string | null;
  is_active?: boolean;
  order?: number;
  meta_title?: string;
  meta_description?: string;
  meta_keywords?: string;
}

interface HowToHelpSectionComponentProps {
  data: HowToHelpSectionData;
  onOpenDonationModal: () => void;
}

// Mapa de componentes: Conecta el 'identifier' de la DB con el Componente de React
const COMPONENT_MAP: Record<string, React.ComponentType<HowToHelpSectionComponentProps>> = {
  'direct_donations': DirectDonations as React.ComponentType<HowToHelpSectionComponentProps>,
  'how_to_help': HowToHelp as React.ComponentType<HowToHelpSectionComponentProps>,
  'more_ways_to_help': MoreWaysToHelp as React.ComponentType<HowToHelpSectionComponentProps>,
  'social_media_help': SocialMediaHelp as React.ComponentType<HowToHelpSectionComponentProps>,
  'qr_donation_section': QRDonationSection as React.ComponentType<HowToHelpSectionComponentProps>,
  'alliances': Alliances as React.ComponentType<HowToHelpSectionComponentProps>,
};

export default function HelpPage() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [sections, setSections] = useState<HowToHelpSectionData[]>([]);
  const [loading, setLoading] = useState(true);
  const openModal = () => setIsModalOpen(true);
  const closeModal = () => setIsModalOpen(false);
  useEffect(() => {
      fetch(`${API_BASE_URL}/api/how-to-help-sections`)
        .then(res => res.json())
        .then(data => {
          // Asumiendo que la API devuelve un array de objetos con {identifier, is_active}
          setSections(data);
          setLoading(false);
        })
        .catch(err => {
          console.error("Error cargando secciones:", err);
          setLoading(false);
        });
    }, []);

  return (
      <main className="bg-white">
        <Navbar onOpenDonationModal={openModal} />
        <Advertisements />

        {loading ? (
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
        </div>
      ) : (
        sections.map((section) => {
          if (section.is_active === false) return null;

          if (section.identifier === 'help_hero') {
            return (
              <HelpHero
                key={section.id}
                title={section.title ?? 'TU AYUDA HACE LA DIFERENCIA'}
                subtitle={section.subtitle ?? 'Hay muchas formas de ser parte del cambio. Encuentra la tuya.'}
                image={section.image?.trim() ? section.image : '/IMG/help-hero-bg.jpg'}
              />
            );
          }

          const Component = COMPONENT_MAP[section.identifier];

          if (Component) {
            return (
              <Component 
                key={section.id} 
                data={section}
                onOpenDonationModal={openModal} 
              />
            );
          }

          return (
            <GenericSection key={section.id} data={section} />
          );
        })
      )}
        
        <Footer onOpenDonationModal={openModal} />
        <DonationModal isOpen={isModalOpen} onClose={closeModal} />
      </main>
  );
}