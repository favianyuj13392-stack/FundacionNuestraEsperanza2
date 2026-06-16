"use client";
import React, { useState, useEffect } from 'react';
import Image from 'next/image';
import { motion } from 'framer-motion';
import { API_BASE_URL as CENTRAL_API_BASE_URL } from '@/utils/apiBaseUrl';
import Link from 'next/link';

// 1. Definimos la estructura de la Noticia
interface NewsItem {
  id: number;
  title: string;
  content: string;
  image: string | null;
  date: string;
}

const News = () => {
  const [news, setNews] = useState<NewsItem[]>([]);
  const [loading, setLoading] = useState(true);

  // URL del Backend
  const API_URL = `${CENTRAL_API_BASE_URL}/api`;

  // 2. Cargar Datos
  useEffect(() => {
    const fetchNews = async () => {
      try {
        const response = await fetch(`${API_URL}/news`);
        if (response.ok) {
          const data = await response.json();
          // Solo mostramos las 3 más recientes en la página de inicio
          setNews(data.slice(0, 3)); 
        }
      } catch (error) {
        console.error("Error cargando noticias:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchNews();
  }, []);

  // Función para limpiar HTML y crear un resumen corto
  const getExcerpt = (htmlContent: string) => {
    // Si no hay ventana (servidor), retornamos vacío para evitar error
    if (typeof window === "undefined") return ""; 
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = htmlContent;
    const text = tempDiv.textContent || tempDiv.innerText || "";
    return text.length > 100 ? text.substring(0, 100) + "..." : text;
  };

  return (
    <section id="noticias" className="relative py-16 bg-gradient-to-br from-rosa-claro/40 via-white to-celeste-claro/60 overflow-hidden">
      <div className="absolute left-0 top-10 h-40 w-40 rounded-full bg-amarillo-detalle/20 blur-3xl"></div>
      <div className="absolute right-0 bottom-10 h-56 w-56 rounded-full bg-turquesa-secundario/20 blur-3xl"></div>
      <div className="container mx-auto px-6">
        
        {/* Título de la Sección */}
        <div className="text-center mb-12">
          <motion.h2 
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="text-3xl md:text-4xl font-bold text-black mb-4 font-title"
          >
            ÚLTIMAS NOTICIAS
          </motion.h2>
          <div className="flex justify-center">
            <div className="bg-rosa-principal w-20 h-2"></div>
          </div>
        </div>

        {/* Contenido Dinámico */}
        {loading ? (
            <div className="text-center text-gray-500 py-10">Cargando noticias...</div>
        ) : news.length === 0 ? (
            <div className="text-center text-gray-500 py-10">Pronto publicaremos nuevas historias.</div>
        ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {news.map((item, index) => (
                <motion.div
                  key={item.id}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ duration: 0.5, delay: index * 0.1 }}
                  whileHover={{ y: -10, boxShadow: "0px 15px 30px rgba(0,0,0,0.1)" }}
                  className="bg-white border border-gray-100 rounded-xl shadow-lg overflow-hidden flex flex-col h-full"
                >
                  {/* Imagen */}
                  <div className="relative h-56 w-full bg-gray-200">
                    {item.image ? (
                        <Image 
                          src={item.image} 
                          alt={item.title} 
                          fill 
                          className="object-cover transition-transform duration-500 hover:scale-105"
                        />
                    ) : (
                        <div className="flex items-center justify-center h-full text-gray-400">
                            Sin Imagen
                        </div>
                    )}
                    
                    {/* Fecha flotante */}
                    <div className="absolute top-4 right-4 bg-rosa-principal backdrop-blur text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm border border-gray-200">
                        {item.date || 'Reciente'}
                    </div>
                  </div>

                  {/* Texto */}
                  <div className="p-6 flex flex-col flex-grow">
                    <h3 className="text-xl font-bold font-title mb-3 text-azul-marino line-clamp-2">
                      {item.title}
                    </h3>
                    
                    {/* Resumen */}
                      <div className="text-gray-600 font-sans text-sm mb-4 line-clamp-3 flex-grow">
                           {/* Renderizamos un resumen plano sin HTML usando getExcerpt */}
                           <p className="whitespace-pre-wrap">{getExcerpt(item.content)}</p>
                      </div>

                    <Link href="/news" className="text-rosa-principal font-bold hover:text-amarillo-detalle transition-colors self-start mt-auto flex items-center gap-1">
                       LEER NOTA COMPLETA <span>→</span>
                    </Link>
                  </div>
                </motion.div>
              ))}
            </div>
        )}

        {/* Botón Ver Más */}
        <div className="text-center mt-12">
            <Link href="/news" className="inline-block bg-turquesa-secundario text-white px-8 py-3 rounded-full font-bold hover:bg-azul-marino transition duration-300 font-button">
                VER TODAS LAS NOTICIAS
            </Link>
        </div>

      </div>
    </section>
  );
};

export default News;