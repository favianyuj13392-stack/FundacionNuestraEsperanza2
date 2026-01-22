"use client";
import React, { useState } from 'react'; 
import Image from 'next/image';
import { motion } from 'framer-motion';

// Estilos para el carrusel
import "slick-carousel/slick/slick.css"; 
import "slick-carousel/slick/slick-theme.css";

// Componentes
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer"; 
import Quotes from '@/components/Quotes';
import Suscribe from '@/components/Suscribe'; 
import AboutUs from '@/components/AboutUs';
import DonationModal from '@/components/DonationModal'; 
import MissionVision from '@/components/MissionVision';

const directorio = [
  { name: 'Mónica Mendez Saucedo', role: 'Presidente', image: '/IMG/equipo/directorio/MONICA-PRESIDENTE.jpeg' },
  { name: 'Mary Gloria Rengel Velasco', role: 'Vicepresidente', image: '/IMG/equipo/directorio/MARY-VICE PRESIDENTE.jpeg' },
  { name: 'Nora Virginia Michel de Arteaga', role: 'Tesorera', image: '/IMG/equipo/directorio/NORAH-TESORERA.jpeg' },
  { name: 'María Teresa Quevedo Espinoza', role: 'Secretaria', image: '/IMG/equipo/directorio/MARIATERESA-SECRETARIA.jpeg' }
];

export default function AboutPage() {
  // 3. Estado del Modal
  const [isDonationModalOpen, setIsDonationModalOpen] = useState(false);

  return (
    <main className="overflow-x-hidden">
      {/* 4. Pasar función al Navbar */}
      
      <Navbar onOpenDonationModal={() => setIsDonationModalOpen(true)} />
        <motion.section 
        className="relative h-80 flex items-center justify-center text-white"
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ duration: 1 }}
      >
        <Image src="/IMG/Equipo.jpg" alt="Fondo Quiénes Somos" layout="fill" objectFit="cover" className="-z-10 object-center" />
        <div className="absolute inset-0 bg-black opacity-50"></div>
        <motion.h1 
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.8, delay: 0.3, ease: "easeOut" }}
          className="text-6xl font-bold z-10 font-title"
        >
          Quiénes Somos
          </motion.h1>
      </motion.section>
        
      <AboutUs />
      <MissionVision />
      {/* Directorio */}
      <section className="bg-beige-claro py-16">
        <div className="container mx-auto px-6 text-center">
          <motion.h2 
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: false }}
            transition={{ duration: 0.8 }}
            className="text-3xl md:text-4xl font-bold text-azul-marino mb-12 font-title"
          >
            NUESTRO EQUIPO
          </motion.h2>

          <h3 className="text-3xl font-bold text-azul-marino mb-8 font-title">Directorio</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
            {directorio.map((member, index) => (
              <motion.div 
                key={member.name}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: false }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                whileHover={{ y: -10 }}
                className="bg-white rounded-lg p-6 shadow-lg flex flex-col items-center"
              >
                <div className="relative w-40 h-40 rounded-full overflow-hidden border-4 border-rosa-principal mb-4">
                  <Image src={member.image} alt={member.name} layout="fill" objectFit="cover" />
                </div>
                <h4 className="text-xl font-bold text-azul-marino font-title">{member.name}</h4>
                <p className="text-turquesa-secundario font-bold font-sans">{member.role}</p>
              </motion.div>
            ))}
          </div>

        </div>
      </section>

      <Quotes/>
      <Suscribe />
      
      {/* 5. Pasar función al Footer */}
      <Footer onOpenDonationModal={() => setIsDonationModalOpen(true)} />
      
      {/* 6. Renderizar Modal */}
      <DonationModal 
        isOpen={isDonationModalOpen} 
        onClose={() => setIsDonationModalOpen(false)} 
      />
    </main>
  );
}