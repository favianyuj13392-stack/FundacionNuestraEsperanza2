/* eslint-disable @typescript-eslint/no-explicit-any */
"use client";
import React, { useEffect, useState, useCallback } from 'react';
import Image from 'next/image';
import { X,  ChevronLeft, ChevronRight, BellRing } from 'lucide-react';

const imageLoader = ({ src }: { src: string }) => src;

const Advertisements = () => {
  const [ads, setAds] = useState<any[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [selectedAd, setSelectedAd] = useState<any>(null);
  const [isVisible, setIsVisible] = useState(true);

  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/advertisements')
      .then(res => res.json())
      .then(data => {
        // Filtramos solo los activos
        const activeAds = data.filter((a: any) => a.is_active);
        setAds(activeAds);
      })
      .catch(err => console.error("Error:", err));
  }, []);

  // Lógica de Carrusel Automático
  const nextAd = useCallback(() => {
    setCurrentIndex((prev) => (prev + 1) % ads.length);
  }, [ads.length]);

  useEffect(() => {
    if (ads.length > 1) {
      const timer = setInterval(nextAd, 5000); // Cambia cada 5 segundos
      return () => clearInterval(timer);
    }
  }, [ads.length, nextAd]);

  if (ads.length === 0 || !isVisible) return null;

  return (
    <>
      <section 
        id="anuncio"  
        className="fixed top-[80px] left-0 right-0 z-50 bg-[#FFF5F7] border-b-2 border-rosa-principal/20 py-3 shadow-md"
      >
      <div className="container mx-auto px-6">
        <div className="flex items-center gap-4">
          
          {/* Miniatura llamativa tipo "Ticket" */}
          <div 
            className="flex-shrink-0 relative group cursor-pointer"
            onClick={() => setSelectedAd(ads[currentIndex])}
          >
            <div className="h-14 w-14 rounded-2xl bg-rosa-principal rotate-3 group-hover:rotate-0 transition-transform flex items-center justify-center shadow-lg overflow-hidden border-2 border-white">
              {ads[currentIndex].image_url ? (
                <Image
                  loader={imageLoader}
                  src={ads[currentIndex].image_url}
                  alt={ads[currentIndex].title || 'anuncio'}
                  width={56}
                  height={56}
                  className="h-full w-full object-cover"
                />
              ) : (
                <BellRing className="text-white" size={24} />
              )}
            </div>
            <div className="absolute -top-1 -right-1 h-4 w-4 bg-turquesa-secundario rounded-full animate-ping"></div>
          </div>

          {/* Texto con el estilo rosado recuperado */}
          <div className="flex-1 min-w-0">
            <span className="text-[11px] font-black text-rosa-principal uppercase tracking-[0.2em]">¡Atención!</span>
            <h3 className="text-azul-marino font-bold text-sm md:text-lg truncate">
              {ads[currentIndex].title}
            </h3>
          </div>

          <div className="flex items-center gap-3">
            <div className="flex bg-white/50 rounded-full p-1 border border-rosa-principal/10">
                <button onClick={() => setCurrentIndex((prev) => (prev - 1 + ads.length) % ads.length)} className="p-1.5 hover:text-rosa-principal transition"><ChevronLeft size={20}/></button>
                <button onClick={() => setCurrentIndex((prev) => (prev + 1) % ads.length)} className="p-1.5 hover:text-rosa-principal transition"><ChevronRight size={20}/></button>
            </div>
            <button onClick={() => setIsVisible(false)} className="text-gray-400 hover:text-rosa-principal">
              <X size={22}/>
            </button>
          </div>
        </div>
      </div>
      
      {/* POP-UP / MODAL (Detalles Completos) */}
      {selectedAd && (
        <div className="fixed inset-0 z-9999 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-fade-in">
          <div className="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative animate-scale-up">
            <button 
              onClick={() => setSelectedAd(null)}
              className="absolute top-3 right-3 z-9999 bg-gray-100 hover:bg-gray-200 text-gray-800 p-2 rounded-full transition-colors"
            >
              <X size={20} />
            </button>

            {/* Imagen: Aquí usamos el image_url procesado por Laravel */}
            <div className="h-60 w-full relative bg-gray-100">
              <Image
                loader={imageLoader}
                src={selectedAd.image_url || 'https://via.placeholder.com/600x400?text=No+Image'}
                alt={selectedAd.title}
                width={600}
                height={240}
                className="w-full h-full object-cover"
              />
            </div>
            
            <div className="p-6">
              <h2 className="text-2xl font-bold text-gray-900 mb-3">{selectedAd.title}</h2>
              
              {/* Descripción (Contenido enriquecido) */}
              <div 
                className="text-gray-600 text-sm mb-6 leading-relaxed max-h-40 overflow-y-auto pr-2"
                dangerouslySetInnerHTML={{ __html: selectedAd.content || selectedAd.description }}
              />
              
              {selectedAd.link_url && (
                <a 
                  href={selectedAd.link_url}
                  target="_blank"
                  className="block w-full text-center bg-rosa-principal text-white font-bold py-3 rounded-xl hover:shadow-lg transition-all"
                >
                  Ir al enlace
                </a>
              )}
            </div>
          </div>
        </div>
      )}
    </section>
    </>
  );
};

export default Advertisements;
