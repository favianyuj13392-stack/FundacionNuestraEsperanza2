"use client";
import React, { useState, useEffect } from 'react';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import Alliances from '@/components/Alliances';
import Contact from '@/components/Contact';
import Subscribe from '@/components/Suscribe';
import HowToHelp from '@/components/HowToHelp';
import DonationModal from '@/components/DonationModal';
import ProgramModal from '@/components/ProgramModal'; 
import ProgramsHero from '@/components/ProgramsHero';
import ProgramsSection, { Program } from '@/components/ProgramsSection';
import { API_BASE_URL } from '@/utils/apiBaseUrl';
import Advertisements from '@/components/Advertisement';
import GenericSection from '@/components/GenericSection';

export interface ProgramsSectionData {
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

interface ProgramsSectionComponentProps {
  data: ProgramsSectionData;
  onOpenDonationModal: () => void;
}

const COMPONENT_MAP: Record<string, React.ComponentType<ProgramsSectionComponentProps>> = {
  'how_to_help': HowToHelp as React.ComponentType<ProgramsSectionComponentProps>,
  'alliances': Alliances as React.ComponentType<ProgramsSectionComponentProps>,
  'subscribe': Subscribe as React.ComponentType<ProgramsSectionComponentProps>,
  'contact': Contact as React.ComponentType<ProgramsSectionComponentProps>,
};

export default function ProgramsPage() {
    // Estados para los Modales
    const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);
    const [selectedProgram, setSelectedProgram] = useState<Program | null>(null);

    const [sections, setSections] = useState<ProgramsSectionData[]>([]);
    const [loading, setLoading] = useState(true);
    const openModal = () => setIsDonationModalOpen(true);
    const closeModal = () => setIsDonationModalOpen(false);

    useEffect(() => {
        fetch(`${API_BASE_URL}/api/programs-sections`)
          .then(res => res.json())
          .then(data => {
            setSections(data);
            setLoading(false);
          })
          .catch(err => {
            console.error("Error loading programs sections:", err);
            setLoading(false);
          });
    }, []);

    return (
        <main>
            <Advertisements />
            <Navbar onOpenDonationModal={openModal} />
            <div className="h-[90px]"></div>

            {loading ? (
              <div className="flex justify-center items-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-rosa-principal"></div>
              </div>
            ) : (
            sections.map((section) => {
              if (section.is_active === false) return null;

              if (section.identifier === 'programs_hero') {
                return (
                  <ProgramsHero
                    key={section.id}
                    title={section.title ?? 'Nuestros Programas'}
                    subtitle={section.subtitle ?? 'Proyectos diseñados para brindar esperanza y soporte integral.'}
                  />
                );
              }

              if (section.identifier === 'programs_section') {
                return (
                  <ProgramsSection key={section.id} onOpenProgramModal={setSelectedProgram} />
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

            {/* Modales */}
            <DonationModal 
              isOpen={isDonationModalOpen} 
              onClose={closeModal} 
            />

            <ProgramModal 
                isOpen={!!selectedProgram} 
                program={selectedProgram} 
                onClose={() => setSelectedProgram(null)} 
            />
        </main>
    );
}