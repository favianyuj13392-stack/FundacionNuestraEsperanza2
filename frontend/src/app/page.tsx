"use client";
import React, { useState, useEffect } from 'react';
import Navbar from "@/components/Navbar";
import Hero from "@/components/Hero";
import Stats from "@/components/Stats";
import AboutUs from "@/components/AboutUs";
import Programs from "@/components/Programs";
import HowToHelp from "@/components/HowToHelp";
import Testimonials from "@/components/Testimonials";
import News from "@/components/News";
import Contact from "@/components/Contact";
import Subscribe from "@/components/Suscribe";
import Alliances from "@/components/Alliances";
import Footer from "@/components/Footer";
import DonationModal from '@/components/DonationModal';
import Advertisements from '@/components/Advertisement';
import GenericSection from '@/components/GenericSection';
import { API_BASE_URL } from '@/utils/apiBaseUrl';

interface HomeSectionData {
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

interface HomeSectionComponentProps {
  data: HomeSectionData;
  onOpenDonationModal: () => void;
}

// Mapa de componentes: Conecta el 'identifier' de la DB con el Componente de React
const COMPONENT_MAP: Record<string, React.ComponentType<HomeSectionComponentProps>> = {
  'hero': Hero as React.ComponentType<HomeSectionComponentProps>,
  'stats': Stats as React.ComponentType<HomeSectionComponentProps>,
  'about_us': AboutUs as React.ComponentType<HomeSectionComponentProps>,
  'programs': Programs as React.ComponentType<HomeSectionComponentProps>,
  'how_to_help': HowToHelp as React.ComponentType<HomeSectionComponentProps>,
  'testimonials': Testimonials as React.ComponentType<HomeSectionComponentProps>,
  'news': News as React.ComponentType<HomeSectionComponentProps>,
  'contact': Contact as React.ComponentType<HomeSectionComponentProps>,
  'subscribe': Subscribe as React.ComponentType<HomeSectionComponentProps>,
  'alliances': Alliances as React.ComponentType<HomeSectionComponentProps>,
};

export default function HomePage() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [sections, setSections] = useState<HomeSectionData[]>([]);
  const [loading, setLoading] = useState(true);

  const openModal = () => setIsModalOpen(true);
  const closeModal = () => setIsModalOpen(false);

  useEffect(() => {
    fetch(`${API_BASE_URL}/api/home-sections`)
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
    <main className="min-h-screen">
      <Navbar onOpenDonationModal={openModal} />
      <Advertisements />
      
      {loading ? (
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
        </div>
      ) : (
        sections.map((section) => {
          if (section.is_active === false) return null;
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

      <Footer />
      <DonationModal isOpen={isModalOpen} onClose={closeModal} />
    </main>
  );
}