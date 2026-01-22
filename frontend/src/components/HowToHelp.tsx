"use client";
import React, { useRef } from 'react';
import Image from 'next/image';
import { motion, useScroll, useTransform } from 'framer-motion';
import Link from 'next/dist/client/link';

// 1. Definimos la interfaz para recibir la función del modal
interface HowToHelpProps {
  onOpenDonationModal: () => void;
}

const HowToHelp: React.FC<HowToHelpProps> = ({ onOpenDonationModal }) => {
  const targetRef = useRef<HTMLDivElement>(null);
  const { scrollYProgress } = useScroll({
    target: targetRef,
    offset: ["start end", "end start"]
  });

  const y = useTransform(scrollYProgress, [0, 1], ["-20%", "20%"]);

  // 2. Número de la fundación 
  const phoneNumber = "59170112236"; 
  const whatsappUrl = `https://wa.me/${phoneNumber}?text=Hola,%20quisiera%20ser%20voluntario%20de%20la%20fundación.`;

  return (
    <section ref={targetRef} id="como-ayudar" className="relative py-20 bg-white min-h-[80vh] overflow-hidden flex items-center">
      <motion.div className="absolute inset-0 z-0" style={{ y }}>
        <Image src="/IMG/ayudar.png" alt="Niños sonriendo" layout="fill" objectFit="cover" quality={85} />
      </motion.div>

      <div className="container mx-auto px-6 relative z-10 h-full flex flex-col justify-center">
        <motion.h2
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: false }}
          transition={{ duration: 0.8 }}
          className="text-3xl md:text-4xl font-bold text-white mb-4 text-center font-title"
        >
          CÓMO AYUDAR
        </motion.h2>
        <div className="flex justify-center mb-12">
          <div className="bg-rosa-principal w-20 h-2"></div>
        </div>

        <div className="grid md:grid-cols-2 gap-12 max-w-5xl mx-auto">
          
          {/* Tarjeta Voluntariado */}
          <motion.div
            initial={{ opacity: 0, y: 50, rotateX: 30 }}
            whileInView={{ opacity: 1, y: 0, rotateX: 0 }}
            viewport={{ once: false, amount: 0.4 }}
            transition={{ duration: 0.8, ease: "easeOut" }}
            className="p-8 rounded-lg shadow-xl text-center bg-black bg-opacity-30 backdrop-blur-sm"
          >
            <h3 className="text-2xl md:text-3xl font-bold text-white mb-4 font-title p-3">VOLUNTARIADO</h3>
            <p className="text-gray-300 font-sans mb-6">
              Únete a nuestro equipo de voluntarios y regala sonrisas. Tu tiempo es el regalo más valioso.
            </p>
            {/* Botón WhatsApp */}
            <a 
              href={whatsappUrl}
              target="_blank"
              rel="noopener noreferrer" 
              className="inline-block bg-white text-azul-marino px-6 py-2 rounded-full font-bold hover:bg-amarillo-detalle transition duration-300 font-button"
            >
              SER VOLUNTARIO
            </a>
          </motion.div>

          {/* Tarjeta Donaciones */}
          <motion.div
            initial={{ opacity: 0, y: 50, rotateX: -30 }}
            whileInView={{ opacity: 1, y: 0, rotateX: 0 }}
            viewport={{ once: false, amount: 0.4 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
            className="p-8 rounded-lg shadow-xl text-center bg-black bg-opacity-30 backdrop-blur-sm"
          >
            <h3 className="text-2xl md:text-3xl font-bold text-white mb-4 font-title p-3">DONACIONES</h3>
            <p className="text-gray-300 font-sans mb-6">
              Cada contribución es un pilar fundamental que sostiene nuestro albergue, alimenta a nuestras familias y financia la esperanza.
            </p>
            {/* Botón Abre el Modal */}
            <button
              onClick={onOpenDonationModal}
              className="bg-rosa-principal text-white px-6 py-2 rounded-full font-bold hover:bg-amarillo-detalle transition duration-300 font-button"
            >
              DONAR
            </button>
          </motion.div>
        </div>

        <div className="text-center mt-12">
            <Link href="/como-ayudar" className="inline-block bg-turquesa-secundario text-white px-8 py-3 rounded-full font-bold hover:bg-azul-marino transition duration-300 font-button">
                VER MÁS
            </Link>
          </div>
      </div>
    </section>
  );
};

export default HowToHelp;