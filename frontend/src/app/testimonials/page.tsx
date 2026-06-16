"use client";
import React, { useState , useEffect} from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import Alliances from '@/components/Alliances';
import DonationModal from '@/components/DonationModal'; 
import Quotes from '@/components/Quotes';
import TestimonialsHero from '@/components/TestimonialsHero';
import TestimonialsSection from '@/components/TestimonialsSection';
import Advertisements from '@/components/Advertisement';
import GenericSection from '@/components/GenericSection';
import { API_BASE_URL } from '@/utils/apiBaseUrl';

interface TestimonialsSectionData {
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

interface TestimonialsSectionComponentProps {
  data: TestimonialsSectionData;
  onOpenDonationModal: () => void;
}
// Mapa de componentes: Conecta el 'identifier' de la DB con el Componente de React
const COMPONENT_MAP: Record<string, React.ComponentType<TestimonialsSectionComponentProps>> = {
  'testimonials_section': TestimonialsSection as React.ComponentType<TestimonialsSectionComponentProps>,
  'quotes': Quotes as React.ComponentType<TestimonialsSectionComponentProps>,
  'alliances': Alliances as React.ComponentType<TestimonialsSectionComponentProps>,
};

export default function TestimonialsPage() {
    const [isModalOpen, setIsModalOpen] = useState(false);
      const [sections, setSections] = useState<TestimonialsSectionData[]>([]);
      const [loading, setLoading] = useState(true);
      const openModal = () => setIsModalOpen(true);
      const closeModal = () => setIsModalOpen(false);

    useEffect(() => {
          fetch(`${API_BASE_URL}/api/testimonials-sections`)
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
      <main>
            <Navbar onOpenDonationModal={openModal} />
            <Advertisements />
            
            {loading ? (
              <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
              </div>
            ) : (
            sections.map((section) => {
              if (section.is_active === false) return null;

              if (section.identifier === 'testimonials_hero') {
                return (
                  <TestimonialsHero
                    key={section.id}
                    title={section.title ?? 'Testimonios'}
                    subtitle={section.subtitle ?? 'Historias reales de esperanza, lucha y superación que nos inspiran cada día.'}
                    image={section.image?.trim() ? section.image : '/IMG/help.jpeg'}
                  />
                );
              }

              if (section.identifier === 'testimonials_section') {
                return (
                  <TestimonialsSection key={section.id} />
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

            <DonationModal 
                isOpen={isModalOpen} 
                onClose={closeModal} 
            />
        </main>
    );
}