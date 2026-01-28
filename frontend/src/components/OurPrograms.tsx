"use client";
import React, { useState } from 'react';
import Image from 'next/image';
import { motion, AnimatePresence } from 'framer-motion';

const programs = [
    {
    id: 1,
    title: "Albergue",
    shortDesc: "Ofrecemos un hogar seguro y cómodo para familias durante el tratamiento oncológico de sus hijos.",
    fullDesc: "Nuestra casita albergue 'UTAJA' (Mi Casa) ofrece un hogar seguro y cálido. Contamos con 11 dormitorios, totalmente equipados para que las familias se sientan cómodas durante el tratamiento oncológico de sus hijos.",
    image: "/IMG/Programs/alojamiento.jpg",
    color: "bg-verde-lima", 
    textColor: "text-verde-lima",
    borderColor: "border-verde-lima"
  },
  {
    id: 2,
    title: "Alimentación y Nutrición",
    shortDesc: "Brindamos comidas nutritivas a niños y padres durante el tratamiento.",
    fullDesc: "Contamos con una amplia cocina donde una encargada prepara alimentos completos y balanceados para padres y niños. Los padres de niños que tienen recomendaciones de nutrición especial, tienen la posibilidad de preparar su alimento en la casita.",
    image: "/IMG/Programs/ALIMENTACION.jpg", 
    color: "bg-rosa-principal",
    textColor: "text-rosa-principal",
    borderColor: "border-rosa-principal"
  },
  {
    id: 3,
    title: "Apoyo Psicológico",
    shortDesc: "Contención emocional y terapia para fortalecer el espíritu durante el tratamiento.",
    fullDesc: "Disponemos de un gabinete de psicología y equipo de psicólogas para apoyar a niños y padres. También se realizan capacitaciones para las familias y voluntarias. El equipo apoya a familias, donde sea que se encuentren, durante la etapa de cuidados paliativos de los pacientes.",
    image: "/IMG/Programs/PSICOLOGIA.JPG",
    color: "bg-turquesa-secundario",
    textColor: "text-turquesa-secundario",
    borderColor: "border-turquesa-secundario"
  },
  {
    id: 4,
    title: "Área de Juegos y Esparcimiento",
    shortDesc: "Un espacio diseñado para la diversión y el descanso de niños y padres.",
    fullDesc: "Contamos con un espacio donde los niños pueden jugar, descansar, al igual que los padres, con mobiliario cómodo, televisión por cable, música y video juegos.",
    image: "/IMG/Programs/AREADEJUEGOS.jpg", 
    color: "bg-azul-marino",
    textColor: "text-azul-marino",
    borderColor: "border-azul-marino"
  }
];

const OurPrograms = () => {
  const [selectedProgram, setSelectedProgram] = useState<typeof programs[0] | null>(null);
  // Estado para controlar qué tarjeta está al frente en el slider
  const [activeIndex, setActiveIndex] = useState(0);

  // Variantes para la animación de entrada de toda la sección
  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.1,
        delayChildren: 0.3
      }
    }
  };

  const cardEntranceVariants = {
    hidden: { y: 100, opacity: 0 },
    visible: { 
      y: 0, 
      opacity: 1,
      transition: { type: "spring" as const, stiffness: 50 } 
    }
  };

  return (
    <section className="py-24 bg-gray-50 relative overflow-hidden font-sans">
      <div className="container mx-auto px-4 md:px-6">
        
        {/* Título */}
        <div className="text-center mb-20">
          <motion.h2 
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-3xl md:text-4xl font-bold text-azul-marino mb-4 font-title"
          >
            NUESTROS PILARES
          </motion.h2>
          <div className="w-20 h-1 bg-amarillo-detalle mx-auto rounded-full"></div>
          <p className="mt-4 text-gray-600 max-w-2xl mx-auto">
            Cuatro áreas fundamentales para un abordaje integral del cáncer infantil.
          </p>
        </div>

        {/* --- CONTENEDOR DEL SLIDER APILADO --- */}
        {/* Usamos un alto fijo relativo para que las cartas absolutas tengan espacio */}
        <motion.div 
          variants={containerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true }}
          className="relative w-full max-w-5xl mx-auto h-[500px] md:h-[450px] flex justify-center items-center"
        >
          {programs.map((program, index) => {
            // --- LÓGICA CENTRAL DEL APILADO ---
            const offset = index - activeIndex;
            const isActive = index === activeIndex;
            
            const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;
            
            const xOffset = isMobile ? 30 : 120; // Cuánto se separan horizontalmente
            const yOffset = isMobile ? 40 : 0;   // Cuánto se separan verticalmente (solo móvil)
            const rotationFactor = isMobile ? 3 : 5; // Grados de rotación

            return (
              <motion.div
                key={program.id}
                variants={cardEntranceVariants}
                // Animamos las propiedades CSS dinámicamente
                animate={{
                  // Posición X: La activa en 0, las otras se mueven a los lados
                  x: offset * xOffset,
                  // Posición Y: En móvil se hace un efecto cascada hacia abajo
                  y: offset * yOffset,
                  // Escala: La activa al 100%, las de atrás más pequeñas
                  scale: 1 - Math.abs(offset) * (isMobile ? 0.05 : 0.1),
                  // Rotación: Efecto abanico
                  rotateZ: offset * rotationFactor,
                  // Z-Index: La activa siempre al frente (usamos 10 como base)
                  zIndex: 10 - Math.abs(offset),
                  // Opacidad: Si están muy lejos (más de 2 posiciones), se desvanecen
                  opacity: Math.abs(offset) > 2 ? 0 : 1
                }}
                transition={{ type: "spring", stiffness: 100, damping: 20 }}
                // Al hacer click en una tarjeta lateral, se vuelve la activa
                onClick={() => setActiveIndex(index)}
                className={`absolute top-0 w-[280px] md:w-[350px] h-[450px] bg-white rounded-2xl shadow-xl overflow-hidden border-b-4 ${program.borderColor} flex flex-col cursor-pointer origin-bottom transition-shadow hover:shadow-2xl`}
                style={{ 
                    // Sombra dinámica: más intensa si está activa
                    boxShadow: isActive ? "0 25px 50px -12px rgba(0, 0, 0, 0.25)" : "0 10px 15px -3px rgba(0, 0, 0, 0.1)"
                }}
              >
                {/* Imagen */}
                <div className="relative h-48 w-full shrink-0">
                  <Image 
                    src={program.image} 
                    alt={program.title} 
                    layout="fill" 
                    objectFit="cover"
                  />
                  <div className={`absolute top-0 right-0 ${program.color} text-white text-xs font-bold px-3 py-1 rounded-bl-lg`}>
                    0{program.id}
                  </div>
                   {/* Overlay si no está activa para que se vea un poco más oscura */}
                  {!isActive && <div className="absolute inset-0 bg-white/30 transition-opacity"></div>}
                </div>

                {/* Contenido */}
                <div className="p-6 flex flex-col flex-grow bg-white">
                  <h3 className={`text-xl font-bold mb-2 font-title ${program.textColor} line-clamp-1`}>
                    {program.title}
                  </h3>
                  <p className="text-gray-600 text-sm mb-4 flex-grow line-clamp-3 leading-relaxed">
                    {program.shortDesc}
                  </p>
                  
                  {/* El botón solo es clickable si la tarjeta está activa, sino el click lo captura el container para activar la tarjeta */}
                  <button
                    onClick={(e) => {
                        e.stopPropagation(); // Evitar que el click active la tarjeta de nuevo
                        if(isActive) setSelectedProgram(program);
                    }}
                    className={`w-full py-2 rounded-full border-2 font-bold text-sm transition-all duration-300 font-button
                      ${program.borderColor} ${program.textColor} 
                      ${isActive 
                        ? `hover:text-white hover:${program.color.replace('bg-', 'bg-')} opacity-100` 
                        : 'opacity-50 cursor-default' // Botón visualmente desactivado si no es la carta principal
                      }
                    `}
                  >
                    VER MÁS DETALLES
                  </button>
                </div>
              </motion.div>
            );
          })}
        </motion.div>

        {/* Indicadores de puntos (opcional, ayuda a la navegación) */}
        <div className="flex justify-center mt-8 space-x-2">
            {programs.map((_, idx) => (
                <button
                    key={idx}
                    onClick={() => setActiveIndex(idx)}
                    className={`w-3 h-3 rounded-full transition-all duration-300 ${idx === activeIndex ? 'bg-azul-marino w-6' : 'bg-gray-300 hover:bg-gray-400'}`}
                />
            ))}
        </div>

      </div>

      {/* --- MODAL (Pop-up) - Sin cambios mayores, funciona igual --- */}
      <AnimatePresence>
        {selectedProgram && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-60 backdrop-blur-sm"
            onClick={() => setSelectedProgram(null)}
          >
            <motion.div
              initial={{ scale: 0.8, opacity: 0, y: 50 }}
              animate={{ scale: 1, opacity: 1, y: 0 }}
              exit={{ scale: 0.9, opacity: 0 }}
              transition={{ type: "spring", damping: 25, stiffness: 300 }}
              className="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden relative flex flex-col md:flex-row max-h-[90vh]"
              onClick={(e) => e.stopPropagation()}
            >
               {/* Botón cerrar */}
               <button 
                onClick={() => setSelectedProgram(null)}
                className="absolute top-4 right-4 z-10 bg-white rounded-full p-2 shadow-md text-gray-800 hover:text-red-500 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              {/* Imagen Modal */}
              <div className="w-full md:w-1/2 relative h-64 md:h-auto shrink-0">
                <Image 
                  src={selectedProgram.image} 
                  alt={selectedProgram.title} 
                  layout="fill" 
                  objectFit="cover"
                />
                <div className={`absolute inset-0 bg-gradient-to-t from-black/70 to-transparent md:hidden`}></div>
                <h3 className="absolute bottom-6 left-6 text-white text-2xl font-bold md:hidden font-title">
                  {selectedProgram.title}
                </h3>
              </div>

              {/* Contenido Modal */}
              <div className="w-full md:w-1/2 p-8 md:p-10 overflow-y-auto">
                <h3 className={`text-3xl font-bold mb-2 font-title hidden md:block ${selectedProgram.textColor}`}>
                  {selectedProgram.title}
                </h3>
                <div className={`w-16 h-1 ${selectedProgram.color} mb-6 hidden md:block`}></div>
                
                <p className="text-gray-600 leading-relaxed font-sans mb-8 text-lg">
                  {selectedProgram.fullDesc}
                </p>

                <div className="mt-auto flex justify-end pt-4 border-t border-gray-100">
                   <button 
                    onClick={() => setSelectedProgram(null)}
                    className="text-gray-500 hover:text-gray-800 font-bold font-button text-sm mr-6"
                   >
                     CERRAR
                   </button>
                   <a href="/como-ayudar" className={`${selectedProgram.color} text-white px-6 py-3 rounded-full font-bold hover:opacity-90 transition-transform hover:scale-105 font-button shadow-md`}>
                     APOYAR ESTE PROGRAMA
                   </a>
                </div>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </section>
  );
};

export default OurPrograms; 