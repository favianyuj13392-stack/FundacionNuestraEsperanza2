"use client";
import React from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';

interface HelpHeroProps {
  title: string;
  subtitle: string;
  image: string;
}

const HelpHero: React.FC<HelpHeroProps> = ({ title, subtitle, image }) => {
  return (
    <motion.section
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 1 }}
      className="relative h-[50vh] md:h-[60vh] flex items-center justify-center text-white"
    >
      <div className="absolute inset-0 z-0">
        <Image 
          src={image} 
          alt="Fondo Cómo Ayudar" 
          fill 
          className="object-cover object-center"
          unoptimized
        />
        <div className="absolute inset-0 bg-black/50 z-10"></div>
      </div>

      <motion.div
        className="relative z-20 text-center px-4"
        initial="hidden"
        animate="visible"
        variants={{
          visible: { transition: { staggerChildren: 0.3 } }
        }}
      >
        <motion.h1
          variants={{
            hidden: { y: 20, opacity: 0 },
            visible: { y: 0, opacity: 1 }
          }}
          className="text-4xl md:text-6xl font-bold font-title mb-4"
        >
          {title}
        </motion.h1>
        <motion.p
          variants={{
            hidden: { y: 20, opacity: 0 },
            visible: { y: 0, opacity: 1 }
          }}
          className="text-xl md:text-2xl font-sans max-w-2xl mx-auto"
        >
          {subtitle}
        </motion.p>
      </motion.div>
    </motion.section>
  );
};

export default HelpHero;