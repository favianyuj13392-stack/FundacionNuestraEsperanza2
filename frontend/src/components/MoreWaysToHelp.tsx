"use client";
import React from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';

const MoreWaysToHelp = () => {
  const waysToHelp = [
    { title: 'Alianzas', description: 'Tu empresa puede ser un aliado estratégico.', image: '/IMG/help.jpeg' },
    { title: 'En Especie', description: 'Aceptamos con gratitud alimentos no perecederos, artículos de higiene y artículos de limpieza.', image: '/IMG/Events/rifaSolidaria.jpg' },
    { title: 'Eventos', description: 'Participa y apoya nuestros eventos de recaudación.', image: '/IMG/Events/Navidad0.jpg' }
  ];

  return (
    <section className="bg-beige-claro py-16 md:py-20">
      <div className="container mx-auto px-6">
        <motion.h2
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: false }}
          transition={{ duration: 0.8 }}
          className="text-3xl md:text-4xl font-bold text-black mb-12 text-center font-title"
        >
          Más Formas de Involucrarte
        </motion.h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {waysToHelp.map((item, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, y: 50 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: false }}
              transition={{ duration: 0.8, delay: index * 0.2 }}
              whileHover={{ y: -10, boxShadow: "0px 20px 30px rgba(0,0,0,0.1)" }}
              className="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col"
            >
              <div className="relative w-full h-56">
                <Image src={item.image} alt={item.title} fill className="object-cover" />
              </div>
              <div className="p-6 text-center flex-grow flex flex-col justify-between">
                <h3 className="text-2xl font-bold font-title mb-3">{item.title}</h3>
                <p className="font-sans text-gray-700">{item.description}</p>
              </div>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default MoreWaysToHelp;