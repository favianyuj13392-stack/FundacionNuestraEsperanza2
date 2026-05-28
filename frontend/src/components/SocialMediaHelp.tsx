"use client";
import React from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';

const SocialMediaHelp = () => {
  return (
    <section className="bg-rosa-principal py-16 text-white text-center">
      <div className="container mx-auto px-6">
        <motion.h2
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          className="text-3xl font-bold mb-6 font-title"
        >
          DIFUNDE LA VOZ
        </motion.h2>
        
        <motion.p
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: false }}
          transition={{ duration: 0.8, delay: 0.2 }}
          className="font-sans text-base md:text-lg mb-8"
        >
          Una simple acción puede generar una ola de esperanza. Comparte nuestra misión y ayúdanos a llegar a más corazones.
        </motion.p>
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: false }}
          transition={{ duration: 0.8, delay: 0.4 }}
          className="flex justify-center space-x-6"
        >
          <a href="https://www.facebook.com/NuestraEsperanzaBo/?locale=es_LA" target="_blank" rel="noopener noreferrer" className="text-4xl hover:scale-125 transition-transform">
            <Image src="/IMG/ic_facebook.png" alt='Facebook' width={50} height={50} />
          </a>
          <a href="https://www.tiktok.com/@fund.nuestra.esperanza?_t=ZM-90VE1jzCdp5&_r=1" target="_blank" rel="noopener noreferrer" className='text-4xl hover:scale-125 transition-transform'>
            <Image src="/IMG/ic_tiktok.png" alt='TikTok' width={50} height={50} />
          </a>
          <a href="https://www.instagram.com/fundacionnuestraesperanza/?hl=es-la" target="_blank" rel="noopener noreferrer" className='text-4xl hover:scale-125 transition-transform'>
            <Image src="/IMG/ic_instagram.png" alt='Instagram' width={50} height={50} />
          </a>
        </motion.div>
      </div>
    </section>
  );
};

export default SocialMediaHelp;