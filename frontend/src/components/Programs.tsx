"use client";
import React, { useState, useEffect } from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';
import Slider from 'react-slick';
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import ProgramModal from './ProgramModal'; 

// Definimos la interfaz aquí o la importamos
interface Program {
  id: number;
  title: string;
  description: string;
  image: string;
  color: string;
}

const Programs = () => {
  // 1. Estado para almacenar programas
  const [programs, setPrograms] = useState<Program[]>([]);
  
  // 2. Estado para el Modal (cuál programa está seleccionado)
  const [selectedProgram, setSelectedProgram] = useState<Program | null>(null);

  const API_URL = 'http://127.0.0.1:8000/api';

  useEffect(() => {
    const fetchPrograms = async () => {
      try {
        const response = await fetch(`${API_URL}/programs`);
        if (response.ok) {
          const data = await response.json();
          setPrograms(data);
        }
      } catch (error) {
        console.error("Error cargando programas:", error);
      }
    };
    fetchPrograms();
  }, []);

  const settings = {
    dots: true,
    infinite: true,
    speed: 500,
    slidesToShow: 3,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 3000,
    responsive: [
      { breakpoint: 1024, settings: { slidesToShow: 2 } },
      { breakpoint: 640, settings: { slidesToShow: 1 } }
    ]
  };

  return (
    <section id="programs" className="relative py-16 bg-gradient-to-br from-celeste-claro via-beige-claro to-rosa-claro overflow-hidden">
      <div className="absolute left-0 top-10 h-40 w-40 rounded-full bg-rosa-principal/20 blur-3xl"></div>
      <div className="absolute right-0 top-24 h-56 w-56 rounded-full bg-turquesa-secundario/20 blur-3xl"></div>
      <div className="container mx-auto px-6">
        <h2 className="text-3xl md:text-4xl font-bold text-black mb-12 text-center font-title">
          NUESTROS PROGRAMAS
        </h2>

        {programs.length > 0 ? (
          <Slider {...settings} className="programs-slider pb-10">
            {programs.map((program) => (
              <div key={program.id} className="px-4 h-full">
                <motion.div
                  whileHover={{ y: -10 }}
                  className="bg-white/95 rounded-3xl shadow-2xl overflow-hidden flex flex-col h-full mx-2 border border-rosa-principal/10"
                >
                  <div className={`w-full h-3 ${program.color || 'bg-blue-500'}`}></div>
                  <div className="relative w-full h-48 overflow-hidden">
                    <Image src={program.image} alt={program.title} layout="fill" objectFit="cover" />
                  </div>
                  <div className="p-6 flex flex-col flex-grow">
                    <h4 className="text-2xl font-bold text-black mb-4 font-title line-clamp-1">{program.title}</h4>
                    
                    {/* Descripción corta (resumen) */}
                    <div className="text-black font-sans mb-6 flex-grow text-sm line-clamp-3">
                        <div dangerouslySetInnerHTML={{ __html: program.description }} />
                    </div>

                    {/* BOTÓN: Ahora abre el Modal */}
                    <button 
                      onClick={() => setSelectedProgram(program)}
                      className="bg-rosa-principal text-white px-6 py-3 rounded-full font-bold hover:bg-amarillo-detalle transition duration-300 font-button self-start"
                    >
                      CONOCER MÁS
                    </button>
                  </div>
                </motion.div>
              </div>
            ))}
          </Slider>
        ) : (
          <p className="text-center text-gray-500">Cargando programas...</p>
        )}

        {/* 3. Renderizar el Modal */}
        <ProgramModal 
          isOpen={!!selectedProgram} 
          program={selectedProgram} 
          onClose={() => setSelectedProgram(null)} 
        />

      </div>
    </section>
  );
};

export default Programs;