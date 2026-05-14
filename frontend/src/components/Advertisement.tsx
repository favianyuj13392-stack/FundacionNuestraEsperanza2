"use client";
import React, { useEffect, useState, useCallback } from 'react';
import { X, Info, ChevronLeft, ChevronRight } from 'lucide-react';

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

  const prevAd = () => {
    setCurrentIndex((prev) => (prev - 1 + ads.length) % ads.length);
  };

  useEffect(() => {
    if (ads.length > 1) {
      const timer = setInterval(nextAd, 5000); // Cambia cada 5 segundos
      return () => clearInterval(timer);
    }
  }, [ads.length, nextAd]);

  if (ads.length === 0 || !isVisible) return null;

  const currentAd = ads[currentIndex];

  return (
    <>
      {/* BANNER TIPO CARRUSEL (ARRIBA DEL NAVBAR) */}
      <div className="w-full bg-gradient-to-r from-rosa-principal to-pink-500 text-white border-b border-white/10 relative overflow-hidden">
        <div className="container mx-auto px-4 py-2">
          <div className="flex items-center justify-between min-h-[40px] gap-4">
            
            {/* Controles si hay más de uno */}
            {ads.length > 1 && (
              <div className="flex gap-1">
                <button onClick={prevAd} className="hover:bg-white/20 p-1 rounded-full transition"><ChevronLeft size={16}/></button>
                <button onClick={nextAd} className="hover:bg-white/20 p-1 rounded-full transition"><ChevronRight size={16}/></button>
              </div>
            )}

            {/* Contenido Central: Compacto */}
            <div className="flex-1 flex items-center justify-center gap-3 overflow-hidden text-center">
              <span className="hidden sm:inline-block bg-white/20 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-tighter">
                Nuevo
              </span>
              <p className="text-sm md:text-base font-medium truncate animate-fade-in">
                {currentAd.title}
              </p>
              <button 
                onClick={() => setSelectedAd(currentAd)}
                className="text-xs font-bold underline hover:text-white/80 transition-all whitespace-nowrap"
              >
                Ver detalle
              </button>
            </div>

            {/* Botón Cerrar */}
            <button onClick={() => setIsVisible(false)} className="p-1 hover:bg-white/10 rounded-full">
              <X size={18} />
            </button>
          </div>
        </div>
      </div>

      {/* POP-UP / MODAL (Detalles Completos) */}
      {selectedAd && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm animate-fade-in">
          <div className="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative animate-scale-up">
            <button 
              onClick={() => setSelectedAd(null)}
              className="absolute top-3 right-3 z-10 bg-gray-100 hover:bg-gray-200 text-gray-800 p-2 rounded-full transition-colors"
            >
              <X size={20} />
            </button>

            {/* Imagen: Aquí usamos el image_url procesado por Laravel */}
            <div className="h-60 w-full relative bg-gray-100">
              <img 
                src={selectedAd.image_url || '/placeholder-img.png'} 
                alt={selectedAd.title}
                className="w-full h-full object-cover"
                onError={(e) => {
                    (e.target as HTMLImageElement).src = 'https://via.placeholder.com/600x400?text=No+Image';
                }}
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
          <div className="absolute inset-0 -z-10" onClick={() => setSelectedAd(null)}></div>
        </div>
      )}
    </>
  );
};

export default Advertisements;