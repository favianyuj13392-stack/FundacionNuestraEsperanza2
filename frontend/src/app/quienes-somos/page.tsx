"use client";
import React, { useState, useEffect } from 'react'; 
// Componentes
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer"; 
import Quotes from '@/components/Quotes';
import Subscribe from '@/components/Suscribe'; 
import AboutUs from '@/components/AboutUs';
import DonationModal from '@/components/DonationModal'; 
import MissionVision from '@/components/MissionVision';
import OurPrograms from '@/components/OurPrograms';
import { API_BASE_URL } from '@/utils/apiBaseUrl';
import AboutHero from '@/components/AboutHero';
import TeamDirectory from '@/components/TeamDirectory';
import Advertisements from '@/components/Advertisement';
import GenericSection from '@/components/GenericSection';

export interface AboutUsSectionData {
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
interface AboutUsSectionComponentProps {
  data: AboutUsSectionData;
  onOpenDonationModal: () => void;
}
// Mapa de componentes: Conecta el 'identifier' de la DB con el Componente de React
const COMPONENT_MAP: Record<string, React.ComponentType<AboutUsSectionComponentProps>> = {
  'about_us': AboutUs as React.ComponentType<AboutUsSectionComponentProps>,
  'mission_vision': MissionVision as React.ComponentType<AboutUsSectionComponentProps>,
  'our_programs': OurPrograms as React.ComponentType<AboutUsSectionComponentProps>,
  'team_directory': TeamDirectory as React.ComponentType<AboutUsSectionComponentProps>,
  'quotes': Quotes as React.ComponentType<AboutUsSectionComponentProps>,
  'subscribe': Subscribe as React.ComponentType<AboutUsSectionComponentProps>,
};

export default function AboutPage() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [sections, setSections] = useState<AboutUsSectionData[]>([]);
  const [loading, setLoading] = useState(true);
  const openModal = () => setIsModalOpen(true);
  const closeModal = () => setIsModalOpen(false);

  useEffect(() => {
        fetch(`${API_BASE_URL}/api/about-us-sections`)
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
      <main className="overflow-x-hidden">
        <Advertisements />
        <Navbar onOpenDonationModal={openModal} />
        <div className="h-[100px]"></div> {/* Espaciador para el Advertisement fijo */}
        
        {loading ? (
          <div className="flex justify-center items-center h-64">
            <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
          </div>
        ) : (
        sections.map((section) => {
          if (section.is_active === false) return null;

          if (section.identifier === 'about_us_hero') {
            return (
              <AboutHero
                key={section.id}
                title={section.title ?? 'Quiénes Somos'}
                subtitle={section.subtitle ?? 'Somos una fundación comprometida con brindar apoyo integral a los niños, niñas y adolescentes en situación de cáncer.'}
                image={section.image?.trim() ? section.image : '/IMG/Equipo.jpg'}
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
};