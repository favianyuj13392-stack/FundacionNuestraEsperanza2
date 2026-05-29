"use client";
import React, { useState, useEffect } from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import Alliances from '@/components/Alliances';
import DonationModal from '@/components/DonationModal';
import NewsHero from '@/components/NewsHero';
import NewsSection from '@/components/NewsSection';
import { API_BASE_URL } from '@/utils/apiBaseUrl';
import Advertisements from '@/components/Advertisement';
import GenericSection from '@/components/GenericSection';

export interface NewsSectionData {
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

interface NewsSectionComponentProps {
  data: NewsSectionData;
  onOpenDonationModal: () => void;
}

// Mapa de componentes: Conecta el 'identifier' de la DB con el Componente de React
const COMPONENT_MAP: Record<string, React.ComponentType<NewsSectionComponentProps>> = {
    'news_section': NewsSection as React.ComponentType<NewsSectionComponentProps>,
    'alliances': Alliances as React.ComponentType<NewsSectionComponentProps>,
};

export default function NewsPage() {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [sections, setSections] = useState<NewsSectionData[]>([]);
    const [loading, setLoading] = useState(true);
    const openModal = () => setIsModalOpen(true);
    const closeModal = () => setIsModalOpen(false);

    useEffect(() => {
        fetch(`${API_BASE_URL}/api/news-sections`)
          .then(res => res.json())
          .then(data => {
            setSections(data);
            setLoading(false);
          })
          .catch(err => {
            console.error("Error loading news sections:", err);
            setLoading(false);
          });
    }, []);

    return (
        <main>
            <Advertisements />
            <Navbar onOpenDonationModal={openModal} />
            <div className="h-[70px]"></div> {/* Espaciador para el Advertisement fijo */}

            {loading ? (
              <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
              </div>
            ) : (
            sections.map((section) => {
              if (section.is_active === false) return null;

              if (section.identifier === 'news_hero') {
                return (
                  <NewsHero
                    key={section.id}
                    title={section.title ?? 'Noticias y Eventos'}
                    subtitle={section.subtitle ?? 'Mantente al día con nuestras actividades y logros.'}
                    image={section.image?.trim() ? section.image : '/IMG/historia.jpg'}
                  />
                );
              }

              if (section.identifier === 'news_section') {
                return (
                  <NewsSection key={section.id} />
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