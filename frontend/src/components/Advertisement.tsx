"use client";
import React, { useEffect, useState } from 'react';
import { X, ExternalLink, Info } from 'lucide-react'; 

const Advertisements = () => {
  const [ads, setAds] = useState<any[]>([]);
  const [selectedAd, setSelectedAd] = useState<any>(null);
  const [isVisible, setIsVisible] = useState(true);

  useEffect(() => {
    fetch('http://127.0.0.1:8000/api/advertisements')
      .then(res => res.json())
      .then(data => {
        // Solo tomamos el anuncio más reciente para el banner superior
        if (data.length > 0) setAds([data[0]]);
      })
      .catch(err => console.error("Error cargando anuncios:", err));
  }, []);

  if (ads.length === 0 || !isVisible) return null;  

  const ad = ads[0];

  return (
    <>
      {/* 1. STICKY BANNER SUPERIOR */}
      <div className="fixed top-0 left-0 w-full z-[60] animate-fade-in-down">
        <div className="bg-gradient-to-r from-rosa-principal to-pink-400 text-white shadow-lg border-b border-white/20">
          <div className="container mx-auto px-4 py-2 flex items-center justify-between">
            
            {/* Contenido Izquierda: Imagen mini + Título */}
            <div className="flex items-center gap-3 overflow-hidden">
              {ad.image_url && (
                <img 
                  src={ad.image_url} 
                  alt="" 
                  className="w-10 h-10 rounded-full object-cover border-2 border-white/50 hidden sm:block shadow-sm"
                />
              )}
              <div className="flex flex-col">
                <span className="text-[10px] uppercase tracking-widest opacity-80 font-bold">Novedad</span>
                <h3 className="text-sm md:text-base font-semibold truncate max-w-[200px] md:max-w-md">
                  {ad.title}
                </h3>
              </div>
            </div>

            {/* Centro/Derecha: CTA con Microanimación */}
            <div className="flex items-center gap-4">
              <button 
                onClick={() => setSelectedAd(ad)}
                className="group relative flex items-center gap-2 bg-white text-rosa-principal px-4 py-1.5 rounded-full text-xs md:text-sm font-bold transition-all hover:scale-105 active:scale-95 shadow-md overflow-hidden"
              >
                <span className="relative z-10">Saber más</span>
                <Info size={14} className="group-hover:rotate-12 transition-transform" />
                <div className="absolute inset-0 bg-gray-100 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
              </button>

              <button 
                onClick={() => setIsVisible(false)}
                className="p-1 hover:bg-white/20 rounded-full transition-colors"
              >
                <X size={18} />
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Espaciador para que el contenido no quede debajo del banner fijo */}
      <div className="h-[56px]"></div>

      {/* 2. MODAL DE DETALLES (Pop-up) */}
      {selectedAd && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
          <div 
            className="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden relative animate-scale-up"
            onClick={(e) => e.stopPropagation()}
          >
            <button 
              onClick={() => setSelectedAd(null)}
              className="absolute top-4 right-4 z-10 bg-black/20 hover:bg-black/40 text-white p-2 rounded-full transition-colors"
            >
              <X size={20} />
            </button>

            <div className="grid md:grid-cols-2">
              <div className="h-64 md:h-auto relative">
                <img 
                  src={selectedAd.image_url} 
                  alt={selectedAd.title}
                  className="absolute inset-0 w-full h-full object-cover"
                />
              </div>
              
              <div className="p-8 flex flex-col justify-center">
                <h2 className="text-2xl font-bold text-gray-900 mb-4">{selectedAd.title}</h2>
                <div 
                  className="text-gray-600 mb-6 text-sm leading-relaxed max-h-48 overflow-y-auto"
                  dangerouslySetInnerHTML={{ __html: selectedAd.description }}
                />
                
                {selectedAd.link_url && (
                  <a 
                    href={selectedAd.link_url}
                    target="_blank"
                    className="flex items-center justify-center gap-2 bg-rosa-principal text-white font-bold py-3 px-6 rounded-xl hover:bg-rosa-dark transition-all shadow-lg hover:shadow-rosa-principal/30"
                  >
                    Ver más información
                    <ExternalLink size={18} />
                  </a>
                )}
              </div>
            </div>
          </div>
          {/* Capa de cierre al hacer clic fuera */}
          <div className="absolute inset-0 -z-10" onClick={() => setSelectedAd(null)}></div>
        </div>
      )}
    </>
  );
};

export default Advertisements;