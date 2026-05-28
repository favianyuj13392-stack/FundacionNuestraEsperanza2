"use client";
import React from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';

interface AboutHeroProps {
  title: string;
  subtitle: string;
  image: string;
}

const AboutHero: React.FC<AboutHeroProps> = ({ title, subtitle, image }) => {
  return (
    <motion.section 
      className="relative h-80 flex items-center justify-center text-white"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 1 }}
    >
      <Image 
        src={image} 
        alt="Fondo Quiénes Somos" 
        fill 
        className="-z-10 object-cover object-center" 
        unoptimized 
      />
      <div className="absolute inset-0 bg-black opacity-50"></div>
      <div className="relative z-10 text-center px-4 max-w-4xl">
        <motion.h1 
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.8, delay: 0.3, ease: "easeOut" }}
          className="text-5xl md:text-6xl font-bold font-title"
        >
          {title}
        </motion.h1>
        <motion.p
          initial={{ y: 20, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.8, delay: 0.5, ease: "easeOut" }}
          className="mt-6 text-lg md:text-xl text-white max-w-3xl mx-auto"
        >
          {subtitle}
        </motion.p>
      </div>
    </motion.section>
  );
};

export default AboutHero;