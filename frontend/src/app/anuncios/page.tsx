/* eslint-disable @typescript-eslint/no-explicit-any */
"use client";
import React, { useEffect, useState } from 'react';
import Image from 'next/image';
import { API_BASE_URL } from '@/utils/apiBaseUrl';
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import { motion } from 'framer-motion';
import { ExternalLink, Sparkles, TrendingUp, X } from 'lucide-react';

const imageLoader = ({ src }: { src: string }) => src;

export default function AnnouncementsPage() {
  const [ads, setAds] = useState([]);
  const [hoveredId, setHoveredId] = useState<number | null>(null);
  const [selectedAd, setSelectedAd] = useState<any>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(6);

  useEffect(() => {
    fetch(`${API_BASE_URL}/api/advertisements`)
      .then(res => res.json())
      .then(data => setAds(data.filter((a: { is_active: boolean }) => a.is_active)));
  }, []);

  const pageCount = Math.max(1, Math.ceil(ads.length / itemsPerPage));
  const paginatedAds = ads.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

  useEffect(() => {
    if (currentPage > pageCount) {
      setCurrentPage(pageCount);
    }
  }, [pageCount, currentPage]);

  const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.2,
        delayChildren: 0.3,
      },
    },
  };

  const itemVariants: import('framer-motion').Variants = {
    hidden: { opacity: 0, y: 20 },
    visible: {
      opacity: 1,
      y: 0,
      transition: { duration: 0.8, ease: "easeOut" },
    },
  };

  return (
    <main className="min-h-screen bg-beige-claro" id="anuncio">
      <Navbar />
      
      {/* Hero Section Mejorado */}
      <section className="relative overflow-hidden pt-20 pb-32">
        {/* Fondo con gradiente vibrante - diferente al navbar */}
        <div className="absolute inset-0 bg-gradient-to-br from-celeste-fondo via-turquesa-secundario to-verde-lima"></div>
        
        {/* Elementos decorativos animados */}
        <motion.div 
          className="absolute top-10 right-10 w-72 h-72 bg-rosa-principal/30 rounded-full filter blur-3xl"
          animate={{ y: [0, 20, 0] }}
          transition={{ duration: 4, repeat: Infinity }}
        ></motion.div>
        <motion.div 
          className="absolute -bottom-10 -left-10 w-96 h-96 bg-amarillo-detalle/20 rounded-full filter blur-3xl"
          animate={{ y: [0, -20, 0] }}
          transition={{ duration: 5, repeat: Infinity, delay: 0.5 }}
        ></motion.div>

        <div className="relative container mx-auto px-6 z-10">
          <motion.div 
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="text-center mb-8"
          >
            <motion.div
              animate={{ rotate: 360 }}
              transition={{ duration: 20, repeat: Infinity, ease: "linear" }}
              className="flex justify-center mb-6"
            >
              <Sparkles className="text-rosa-principal" size={32} />
            </motion.div>
            <h1 className="text-5xl md:text-6xl font-bold font-title text-azul-marino mb-4">
              Novedades y <span className="bg-gradient-to-r from-rosa-principal to-amarillo-detalle bg-clip-text text-transparent">Anuncios</span>
            </h1>
            <p className="text-xl text-azul-marino/80 max-w-2xl mx-auto font-semibold">
              Entérate de las últimas noticias, eventos y oportunidades de nuestra fundación
            </p>
          </motion.div>

          {/* Estadística flotante */}
          <motion.div 
            initial={{ opacity: 0, scale: 0.8 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 0.4, duration: 0.6 }}
            className="inline-flex items-center gap-3 bg-white/20 backdrop-blur-md border border-white/40 rounded-full px-6 py-3 mx-auto w-fit"
          >
            <TrendingUp className="text-azul-marino" size={20} />
            <span className="text-azul-marino font-bold">{ads.length} Anuncios Activos</span>
          </motion.div>
        </div>
      </section>

      {/* Grid de Anuncios */}
      <section className="container mx-auto px-6 pb-20">
        <div className="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <p className="text-sm uppercase tracking-[0.24em] text-rosa-principal font-bold mb-2">Paginación</p>
            <div className="inline-flex items-center gap-3 rounded-full border border-rosa-principal/20 bg-white/80 p-2 shadow-sm">
              {[4,6, 10].map((limit) => (
                <button
                  key={limit}
                  type="button"
                  onClick={() => { setItemsPerPage(limit); setCurrentPage(1); }}
                  className={`rounded-full px-4 py-2 text-sm font-semibold transition ${itemsPerPage === limit ? 'bg-rosa-principal text-white shadow-lg' : 'text-azul-marino bg-white hover:bg-rosa-principal/10'}`}
                >
                  {limit}
                </button>
              ))}
            </div>
          </div>

          <div className="text-sm text-gray-600">
            Mostrando <span className="font-semibold text-azul-marino">{paginatedAds.length}</span> de <span className="font-semibold text-azul-marino">{ads.length}</span> anuncios activos
          </div>
        </div>

        <motion.div 
          variants={containerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, margin: "0px" }}
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
          {paginatedAds.map((ad: any, index) => (
            <motion.div
              key={ad.id}
              variants={itemVariants}
              onMouseEnter={() => setHoveredId(ad.id)}
              onMouseLeave={() => setHoveredId(null)}
              onClick={() => setSelectedAd(ad)}
              className="group relative h-full cursor-pointer transition-all duration-300 hover:-translate-y-1"
            >
              {/* Tarjeta Principal */}
              <div className="relative h-full bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border border-rosa-principal/10 hover:border-rosa-principal/40">
                
                {/* Imagen con overlay */}
                <div className="relative h-56 overflow-hidden bg-gradient-to-br from-celeste-fondo to-azul-marino flex items-center justify-center">
                  {ad.image_url ? (
                    <Image
                      loader={imageLoader}
                      src={ad.image_url}
                      alt={ad.title}
                      fill
                      className="object-cover w-full h-full transition-transform duration-500 group-hover:scale-110"
                      unoptimized
                    />
                  ) : (
                    <div className="flex flex-col items-center justify-center text-white/50">
                      <Sparkles size={40} className="mb-2 opacity-50" />
                      <span className="font-sans text-sm font-semibold">Sin imagen</span>
                    </div>
                  )}
                  
                  {/* Overlay gradiente */}
                  <div className="absolute inset-0 bg-gradient-to-t from-azul-marino/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                  {/* Badge */}
                  <motion.div 
                    initial={{ x: -20, opacity: 0 }}
                    animate={{ x: 0, opacity: 1 }}
                    transition={{ delay: index * 0.1 + 0.2 }}
                    className="absolute top-4 left-4 bg-gradient-to-r from-rosa-principal to-turquesa-secundario text-white text-xs font-bold px-4 py-2 rounded-full flex items-center gap-2"
                  >
                    <Sparkles size={14} />
                    Destacado
                  </motion.div>

                  {/* Contador */}
                  <div className="absolute bottom-4 right-4 bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-bold px-3 py-1 rounded-full">
                    {index + 1} de {ads.length}
                  </div>
                </div>

                {/* Contenido */}
                <div className="p-6 flex flex-col h-full bg-white relative z-10">
                  <div className="flex-1">
                    <motion.h2 
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      transition={{ delay: index * 0.1 + 0.3 }}
                      className="text-xl font-bold text-azul-marino mb-3 line-clamp-2 group-hover:text-rosa-principal transition-colors"
                    >
                      {ad.title}
                    </motion.h2>
                    
                    <div 
                      className="text-gray-600 text-sm mb-4 line-clamp-3 leading-relaxed"
                      dangerouslySetInnerHTML={{ __html: ad.content || ad.description }}
                    />
                  </div>

                  {/* Botón CTA */}
                  <motion.button
                    type="button"
                    whileHover={{ x: 5 }}
                    className="inline-flex items-center justify-center gap-2 w-full bg-gradient-to-r from-rosa-principal to-turquesa-secundario text-white font-bold py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:from-turquesa-secundario hover:to-rosa-principal mt-auto"
                    onClick={(e) => { e.stopPropagation(); setSelectedAd(ad); }}
                  >
                    <span>Más detalles</span>
                    <ExternalLink size={18} />
                  </motion.button>
                </div>
              </div>
            </motion.div>
          ))}
        </motion.div>

        <div className="mt-10 flex flex-col gap-4 items-center justify-between md:flex-row">
          <p className="text-sm text-gray-600">
            Página <span className="font-semibold text-azul-marino">{currentPage}</span> de <span className="font-semibold text-azul-marino">{pageCount}</span>
          </p>
          <div className="inline-flex items-center gap-2">
            <button
              type="button"
              onClick={() => setCurrentPage((prev) => Math.max(prev - 1, 1))}
              disabled={currentPage === 1}
              className="rounded-full border border-rosa-principal/20 bg-white px-4 py-2 text-sm font-semibold text-azul-marino transition disabled:cursor-not-allowed disabled:opacity-50 hover:bg-rosa-principal/10"
            >
              Anterior
            </button>
            {Array.from({ length: pageCount }, (_, i) => i + 1).map((page) => (
              <button
                key={page}
                type="button"
                onClick={() => setCurrentPage(page)}
                className={`rounded-full px-4 py-2 text-sm font-semibold transition ${currentPage === page ? 'bg-rosa-principal text-white' : 'bg-white text-azul-marino hover:bg-rosa-principal/10'}`}
              >
                {page}
              </button>
            ))}
            <button
              type="button"
              onClick={() => setCurrentPage((prev) => Math.min(prev + 1, pageCount))}
              disabled={currentPage === pageCount}
              className="rounded-full border border-rosa-principal/20 bg-white px-4 py-2 text-sm font-semibold text-azul-marino transition disabled:cursor-not-allowed disabled:opacity-50 hover:bg-rosa-principal/10"
            >
              Siguiente
            </button>
          </div>
        </div>

        {/* Mensaje si no hay anuncios */}
        {ads.length === 0 && (
          <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            className="text-center py-20"
          >
            <p className="text-2xl text-gray-500 font-semibold">No hay anuncios disponibles en este momento</p>
          </motion.div>
        )}
      </section>

      {/* MODAL - Detalles Completos del Anuncio */}
      {selectedAd && (
        <motion.div 
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          onClick={() => setSelectedAd(null)}
          className="fixed inset-0 z-9999 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
        >
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.9 }}
            transition={{ duration: 0.3 }}
            onClick={(e) => e.stopPropagation()}
            className="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden relative"
          >
            {/* Botón Cerrar */}
            <motion.button 
              whileHover={{ scale: 1.1 }}
              whileTap={{ scale: 0.95 }}
              onClick={() => setSelectedAd(null)}
              className="absolute top-6 right-6 z-9999 bg-gradient-to-r from-rosa-principal to-turquesa-secundario hover:shadow-lg text-white p-3 rounded-full transition-all"
            >
              <X size={24} />
            </motion.button>

            {/* Imagen */}
            <motion.div 
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.2 }}
              className="h-80 w-full relative bg-gradient-to-br from-celeste-fondo to-turquesa-secundario overflow-hidden flex items-center justify-center"
            >
              {selectedAd.image_url ? (
                <Image
                  loader={imageLoader}
                  src={selectedAd.image_url}
                  alt={selectedAd.title}
                  fill
                  className="w-full h-full object-cover"
                  unoptimized
                />
              ) : (
                <div className="flex flex-col items-center justify-center text-white/50 z-10">
                  <Sparkles size={60} className="mb-4 opacity-50" />
                  <span className="font-sans text-xl font-semibold">Sin imagen</span>
                </div>
              )}
              <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"></div>
            </motion.div>

            {/* Contenido */}
            <motion.div 
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3 }}
              className="p-8 md:p-10"
            >
              {/* Título */}
              <h2 className="text-4xl font-bold text-azul-marino mb-4">{selectedAd.title}</h2>

              {/* Descripción */}
              <div 
                className="text-gray-700 text-lg leading-relaxed mb-8 max-h-60 overflow-y-auto pr-3 font-sans"
                dangerouslySetInnerHTML={{ __html: selectedAd.content || selectedAd.description }}
              />

              {/* Botón CTA */}
              {selectedAd.link_url && (
                <motion.a
                  href={selectedAd.link_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.98 }}
                  className="inline-flex items-center justify-center gap-3 w-full bg-gradient-to-r from-rosa-principal to-turquesa-secundario text-white font-bold py-4 rounded-xl hover:shadow-xl transition-all duration-300 hover:from-turquesa-secundario hover:to-rosa-principal"
                >
                  <span className="text-lg">Ir al enlace</span>
                  <ExternalLink size={20} />
                </motion.a>
              )}
            </motion.div>
          </motion.div>
        </motion.div>
      )}

      <Footer />
    </main>
  );
}
