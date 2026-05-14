"use client";
import React, { useEffect, useState } from 'react';
import Image from 'next/image';

const Alliances = () => {
  const [alliances, setAlliances] = useState<any[]>([]);

  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/alliances')
      .then(res => res.json())
      .then(data => setAlliances(data))
      .catch(err => console.error("Error cargando alianzas:", err));
  }, []);

  return (
    <section className="bg-white py-16">
      <div className="container mx-auto px-6">
        <h2 className="text-3xl md:text-4xl font-bold text-black mb-3 text-center font-title">NUESTROS ALIADOS</h2>
        <div className="flex justify-center mb-10">
          <div className="bg-pink-500 w-20 h-2"></div>
        </div>
        
        <div className="flex flex-wrap justify-center items-center gap-12">
          {alliances.map((alliance) => (
            <a
              key={alliance.id}
              href={alliance.url}
              target="_blank"
              rel="noopener noreferrer"
              className="grayscale hover:grayscale-0 transition duration-300"
            >
              <img
                src={alliance.logo_url}
                alt={alliance.name}
                className="h-16 w-auto object-contain"
              />
            </a>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Alliances;