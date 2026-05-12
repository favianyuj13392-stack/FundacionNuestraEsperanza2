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

export default function HomePage() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [sections, setSections] = useState<any>({}); // <-- Estado para guardar qué secciones están activas

  const openModal = () => setIsModalOpen(true);
  const closeModal = () => setIsModalOpen(false);

  // <-- Llamada a la API para traer los Toggles
  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/home-sections')
      .then(res => res.json())
      .then(data => setSections(data))
      .catch(err => console.error("Error cargando configuración de secciones:", err));
  }, []);

  return (
    <main>
      {/* El Navbar y Footer no se ocultan, siempre son fijos */}
      <Navbar onOpenDonationModal={openModal} />
      
      {sections.hero !== false && <Hero onOpenDonationModal={openModal} />}
      
      {sections.stats !== false && <Stats />}
      
      {sections.about_us !== false && <AboutUs />}
      
      {sections.programs !== false && <Programs />}
      
      {sections.how_to_help !== false && <HowToHelp onOpenDonationModal={openModal} />}

      {sections.testimonials !== false && <Testimonials />}
      
      {sections.news !== false && <News />}
      
      {sections.contact !== false && <Contact />}
      
      {sections.subscribe !== false && <Subscribe />}
      
      {sections.alliances !== false && <Alliances />}

      {sections.advertisements !== false && <Advertisements />}
      
      <Footer onOpenDonationModal={openModal} />

      <DonationModal isOpen={isModalOpen} onClose={closeModal} />
    </main>
  );
}