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

// Mapa de componentes: Conecta el 'identifier' de la DB con el Componente de React
const COMPONENT_MAP: { [key: string]: React.ComponentType<any> } = {
  'hero': Hero,
  'stats': Stats,
  'about_us': AboutUs,
  'programs': Programs,
  'how_to_help': HowToHelp,
  'testimonials': Testimonials,
  'news': News,
  'contact': Contact,
  'subscribe': Subscribe,
  'alliances': Alliances,
  'advertisements': Advertisements, 
};

export default function HomePage() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [sections, setSections] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  const openModal = () => setIsModalOpen(true);
  const closeModal = () => setIsModalOpen(false);

  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/home-sections')
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
    <main className="min-h-screen bg-white">
      <Navbar onOpenDonationModal={openModal} />
      
      {loading ? (
        <div className="flex justify-center items-center h-64">
          <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
        </div>
      ) : (
        sections.map((section) => {
          const Component = COMPONENT_MAP[section.identifier];
          
          // Solo renderiza si el componente existe y la sección está activa
          if (!Component || section.is_active === false) return null;

          return (
            <Component 
              key={section.id} 
              data={section} // Le pasamos los datos de la DB por si el componente los necesita
              onOpenDonationModal={openModal} 
            />
          );
        })
      )}

      <Footer />
      <DonationModal isOpen={isModalOpen} onClose={closeModal} />
    </main>
  );
}