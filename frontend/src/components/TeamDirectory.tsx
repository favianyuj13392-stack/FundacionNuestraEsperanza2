"use client";
import React from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';

const directorio = [
  { name: 'Mónica Mendez Saucedo', role: 'Presidente', image: '/IMG/equipo/directorio/MONICA-PRESIDENTE.jpeg' },
  { name: 'Mary Gloria Rengel Velasco', role: 'Vicepresidente', image: '/IMG/equipo/directorio/MARY-VICE PRESIDENTE.jpeg' },
  { name: 'Nora Virginia Michel de Arteaga', role: 'Tesorera', image: '/IMG/equipo/directorio/NORAH-TESORERA.jpeg' },
  { name: 'María Teresa Quevedo Espinoza', role: 'Secretaria', image: '/IMG/equipo/directorio/MARIATERESA-SECRETARIA.jpeg' }
];

const TeamDirectory = () => {
  return (
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
                <Image src={member.image} alt={member.name} fill className="object-cover" />
              </div>
              <h4 className="text-xl font-bold text-azul-marino font-title">{member.name}</h4>
              <p className="text-turquesa-secundario font-bold font-sans">{member.role}</p>
            </motion.div>
          ))}
        </div>

      </div>
    </section>
  );
};

export default TeamDirectory;