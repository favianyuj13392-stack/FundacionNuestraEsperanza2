"use client";
import React from 'react';
import { motion } from 'framer-motion';

const MissionVision = () => {
  return (
    <section className="py-20 bg-white overflow-hidden">
      <div className="container mx-auto px-6">
        <div className="text-center mb-16">
          <motion.h2 
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="text-3xl md:text-4xl font-bold text-azul-marino mb-4 font-title"
          >
            NUESTRA ESENCIA
          </motion.h2>
          <div className="flex justify-center">
            <motion.div 
              initial={{ width: 0 }}
              whileInView={{ width: 80 }}
              viewport={{ once: true }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="bg-rosa-principal h-2 rounded-full"
            ></motion.div>
          </div>
        </div>

        <div className="grid md:grid-cols-2 gap-10 items-stretch">
          
          {/* Tarjeta MISIÓN */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: false, amount: 0.3 }}
            transition={{ duration: 0.8, ease: "easeOut" }}
            className="bg-gray-50 rounded-2xl p-8 md:p-12 shadow-lg border-t-8 border-rosa-principal flex flex-col relative overflow-hidden group hover:shadow-2xl transition-shadow duration-300"
          >
            {/* Icono decorativo de fondo (SVG) */}
            <div className="absolute top-0 right-0 -mt-4 -mr-4 opacity-10 group-hover:opacity-20 transition-opacity duration-500">
               <svg width="120" height="120" viewBox="0 0 24 24" fill="currentColor" className="text-rosa-principal">
                 <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
               </svg>
            </div>

            <h3 className="text-2xl md:text-3xl font-bold text-azul-marino mb-6 font-title relative z-10">
              NUESTRA MISIÓN
            </h3>
            <p className="text-gray-700 font-sans leading-relaxed text-lg relative z-10 flex-grow">
              Apoyar al niño, niña y adolescente con cáncer a través de programas de atención integral con el fin de mejorar su calidad de vida.
            </p>
          </motion.div>

          {/* Tarjeta VISIÓN */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: false, amount: 0.3 }}
            transition={{ duration: 0.8, delay: 0.2, ease: "easeOut" }}
            className="bg-gray-50 rounded-2xl p-8 md:p-12 shadow-lg border-t-8 border-turquesa-secundario flex flex-col relative overflow-hidden group hover:shadow-2xl transition-shadow duration-300"
          >
            {/* Icono decorativo de fondo (SVG) */}
            <div className="absolute top-0 right-0 -mt-4 -mr-4 opacity-10 group-hover:opacity-20 transition-opacity duration-500">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="currentColor" className="text-turquesa-secundario">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
            </div>

            <h3 className="text-2xl md:text-3xl font-bold text-azul-marino mb-6 font-title relative z-10">
              NUESTRA VISIÓN
            </h3>
            <p className="text-gray-700 font-sans leading-relaxed text-lg relative z-10 flex-grow">
              Ser una organización sin fines de lucro con un alto nivel de sensibilidad y responsabilidad social, comprometida a luchar por mejorar la calidad de vida del niño, niña y adolescente con cáncer y sus familias.
            </p>
          </motion.div>

        </div>
      </div>
    </section>
  );
};

export default MissionVision;