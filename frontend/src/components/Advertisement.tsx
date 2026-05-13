"use client";
import React, { useEffect, useState } from 'react';

const Advertisements = () => {
  const [ads, setAds] = useState<any[]>([]);

  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/advertisements')
      .then(res => res.json())
      .then(data => setAds(data))
      .catch(err => console.error("Error cargando anuncios:", err));
  }, []);

  if (ads.length === 0) return null;

  return (
    <section className="bg-gray-50 py-16">
      <div className="container mx-auto px-6">
        {/* Título de sección estilo "Programas" */}
        <div className="text-center mb-12">
          <h2 className="text-3xl md:text-4xl font-bold text-black mb-3 font-title uppercase">
            Anuncios y Novedades
          </h2>
          <div className="flex justify-center">
            <div className="bg-rosa-principal w-20 h-2"></div>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {ads.map((ad) => (
            <div key={ad.id} className="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 transition-transform hover:scale-105">
              {/* Imagen del Anuncio (Curator) */}
              {ad.image_url && (
                <div className="relative h-56 w-full">
                  <img 
                    src={ad.image_url} 
                    alt={ad.title}
                    className="w-full h-full object-cover"
                  />
                </div>
              )}
              
              <div className="p-6">
                <h3 className="font-bold text-xl text-gray-900 mb-2">{ad.title}</h3>
                <p className="text-gray-600 text-sm mb-4 line-clamp-3">
                  {ad.description}
                </p>
                
                {ad.link_url && (
                  <a 
                    href={ad.link_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-block bg-rosa-principal text-white px-6 py-2 rounded-full text-sm font-semibold hover:bg-opacity-90 transition"
                  >
                    Leer más
                  </a>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Advertisements;