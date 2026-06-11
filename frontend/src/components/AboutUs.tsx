"use client";
import React from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';
import Link from 'next/dist/client/link';

const AboutUs = () => {
  return (
    <section className="relative overflow-hidden py-16 bg-amarillo-claro/20">
      <div className="absolute -left-10 top-10 h-56 w-56 rounded-full bg-rosa-principal/20 blur-3xl"></div>
      <div className="absolute right-0 top-24 h-72 w-72 rounded-full bg-turquesa-secundario/15 blur-3xl"></div>
      <div className="absolute left-1/2 bottom-0 h-64 w-64 -translate-x-1/2 rounded-full bg-azul-marino/10 blur-3xl"></div>
      <div className="container mx-auto grid md:grid-cols-2 gap-12 items-center p-6 md:p-10">
        <motion.div
          initial={{ opacity: 0, x: -100 }}
          whileInView={{ opacity: 1, x: 0 }}
          viewport={{ once: false, amount: 0.3 }}
          transition={{ duration: 0.8, ease: "easeOut" }}
          className="order-2 md:order-1 bg-white/90 rounded-[2rem] p-8 shadow-2xl backdrop-blur-sm"
        >
          <h2 className="text-4xl md:text-5xl font-bold text-black mb-4 font-title">QUIÉNES SOMOS</h2>
          <div className="bg-rosa-principal w-20 h-2 mb-5"></div>
          <h4 className='text-xl md:text-2xl font-sans text-black'>Misión, Visión y Propósito</h4>
          <p className="text-black font-sans pt-5 pb-5">
            En la Fundación Nuestra Esperanza trabajamos de corazón con el objetivo fundamental de mejorar la calidad de vida y aumentar las probabilidades de sobrevivir de niños, niñas y adolecentes de escasos recursos que padecen de Cáncer.</p>
          <p className='mb-6'>Desde el año 2017 hemos implementado un programa de intervención integral gratuito. Trabajamos brindando a las familias alojamiento, alimentación, apoyo psicosocial, espacios de recreación y otro tipo de ayudas complementarias como ser viveres, y material escolar que permitan hacer su día a día más llevadero y evitar que nuestros niños abandonen el tratamiento.
          </p>
          <div className="text-center mt-12">
            <Link href="/quienes-somos" className="inline-block bg-turquesa-secundario text-white px-8 py-3 rounded-full font-bold hover:bg-azul-marino transition duration-300 font-button">
                CONOCER MÁS
            </Link>
          </div>
        </motion.div>
        <div data-aos="fade-left" className="relative w-full h-80 md:h-96 rounded-[2rem] shadow-2xl overflow-hidden order-1 md:order-2 border border-white/50">
          <motion.div
            className="absolute inset-0"
            initial={{ scale: 1.2 }}
            whileInView={{ scale: 1 }}
            viewport={{ once: false, amount: 0.5 }}
            transition={{ duration: 1, ease: [0.22, 1, 0.36, 1] }} // Curva de easing suave
          >
            <Image
              src="/IMG/Equipo1.jpg"
              alt="Voluntarios de la fundación ayudando"
              layout="fill"
              objectFit="cover"
            />
          </motion.div>
        </div>
      </div>
    </section>
  );
};

export default AboutUs;