"use client";
import React, { useEffect, useState } from 'react';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import { motion } from 'framer-motion';
import { fetchTransparencyDetail, TransparencyDetail } from '@/services/transparencyService';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

export default function TransparenciaDetailPage() {
    const params = useParams();
    const router = useRouter();
    const slug = params.slug as string;

    const [detail, setDetail] = useState<TransparencyDetail | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const loadDetail = async () => {
            if (!slug) return;
            const data = await fetchTransparencyDetail(slug);
            if (!data) {
                // router.push('/transparencia'); // Podría redirigir o mostrar un 404
            }
            setDetail(data);
            setLoading(false);
        };
        loadDetail();
    }, [slug, router]);

    if (loading) {
        return (
            <main className="bg-white min-h-screen flex flex-col">
                <Navbar />
                <div className="flex-grow flex justify-center items-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-turquesa-secundario"></div>
                </div>
                <Footer />
            </main>
        );
    }

    if (!detail) {
        return (
            <main className="bg-white min-h-screen flex flex-col">
                <Navbar />
                <div className="flex-grow flex flex-col justify-center items-center text-center px-6">
                    <h1 className="text-3xl font-bold text-azul-marino mb-4">Campaña no encontrada</h1>
                    <p className="text-gray-600 mb-8">No pudimos cargar los detalles de transparencia para esta campaña.</p>
                    <Link href="/campanas" className="bg-rosa-principal text-white px-6 py-3 rounded-full font-bold hover:bg-opacity-90">
                        Volver a Campañas
                    </Link>
                </div>
                <Footer />
            </main>
        );
    }

    return (
        <main className="bg-gray-50 min-h-screen flex flex-col">
            <Navbar />

            {/* Header / Metadatos */}
            <section className="bg-azul-marino text-white py-16 px-6 mt-16">
                <div className="container mx-auto max-w-5xl">
                    <Link href="/campanas" className="text-turquesa-secundario hover:underline text-sm mb-4 inline-block font-bold">
                        &larr; Volver a Campañas
                    </Link>
                    <motion.h1 
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="text-3xl md:text-4xl font-bold font-title mb-2"
                    >
                        Trazabilidad: {detail.metadata.name}
                    </motion.h1>
                    <p className="text-gray-300 text-lg font-sans max-w-3xl">
                        Revisión de la ejecución de fondos de esta campaña.
                    </p>
                </div>
            </section>

            <div className="container mx-auto max-w-5xl px-6 py-12 flex-grow">
                {/* Cifras Clave */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                    <motion.div 
                        initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }}
                        className="bg-white p-6 rounded-xl shadow-md border-l-4 border-turquesa-secundario"
                    >
                        <h3 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Recaudado</h3>
                        <p className="text-3xl font-bold text-azul-marino">{detail.cifras.total_recaudado} <span className="text-xl text-gray-400">Bs</span></p>
                        <p className="text-sm text-gray-500 mt-2">De una meta de {detail.metadata.monetary_goal} Bs</p>
                    </motion.div>

                    <motion.div 
                        initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}
                        className="bg-white p-6 rounded-xl shadow-md border-l-4 border-rosa-principal"
                    >
                        <h3 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Total Ejecutado</h3>
                        <p className="text-3xl font-bold text-rosa-principal">{detail.cifras.total_ejecutado} <span className="text-xl text-gray-400">Bs</span></p>
                        <p className="text-sm text-gray-500 mt-2">Gastos registrados en la campaña</p>
                    </motion.div>

                    <motion.div 
                        initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.3 }}
                        className={`bg-white p-6 rounded-xl shadow-md border-l-4 ${detail.cifras.saldo_disponible >= 0 ? 'border-green-500' : 'border-red-500'}`}
                    >
                        <h3 className="text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Saldo Disponible</h3>
                        <p className={`text-3xl font-bold ${detail.cifras.saldo_disponible >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {detail.cifras.saldo_disponible} <span className="text-xl text-gray-400">Bs</span>
                        </p>
                        <p className="text-sm text-gray-500 mt-2">Fondos restantes para esta campaña</p>
                    </motion.div>
                </div>

                {/* Descarga de Informe (PDF) */}
                {detail.metadata.report_pdf_url && (
                    <motion.div 
                        initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.4 }}
                        className="bg-turquesa-secundario bg-opacity-10 rounded-xl p-8 mb-12 flex flex-col md:flex-row items-center justify-between border border-turquesa-secundario border-opacity-30"
                    >
                        <div>
                            <h3 className="text-xl font-bold text-azul-marino mb-2">Informe Final Disponible</h3>
                            <p className="text-gray-600">Descarga el informe detallado en formato PDF con todos los soportes y conclusiones de la campaña.</p>
                        </div>
                        <a 
                            href={detail.metadata.report_pdf_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="mt-6 md:mt-0 inline-flex items-center bg-turquesa-secundario text-white px-6 py-3 rounded-full font-bold hover:bg-opacity-90 transition shadow-md whitespace-nowrap"
                        >
                            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Descargar Informe PDF
                        </a>
                    </motion.div>
                )}

                {/* Tabla de Trazabilidad / Historial de Gastos */}
                <motion.div 
                    initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.5 }}
                    className="bg-white rounded-xl shadow-md overflow-hidden"
                >
                    <div className="p-6 border-b border-gray-100">
                        <h2 className="text-2xl font-bold text-azul-marino font-title">Historial de Gastos</h2>
                        <p className="text-sm text-gray-500 mt-1">Registro detallado de la ejecución de fondos</p>
                    </div>

                    {detail.trazabilidad && detail.trazabilidad.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                                        <th className="p-4 font-semibold border-b">Fecha</th>
                                        <th className="p-4 font-semibold border-b">Concepto</th>
                                        <th className="p-4 font-semibold border-b text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {detail.trazabilidad.map((expense, index) => (
                                        <tr key={index} className="border-b border-gray-50 hover:bg-gray-50 transition">
                                            <td className="p-4 text-sm text-gray-700 whitespace-nowrap">
                                                {format(new Date(expense.date), "dd 'de' MMMM, yyyy", { locale: es })}
                                            </td>
                                            <td className="p-4 text-gray-800 font-medium">{expense.description}</td>
                                            <td className="p-4 text-right text-rosa-principal font-bold whitespace-nowrap">
                                                {expense.amount} Bs
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="p-12 text-center text-gray-500">
                            <svg className="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <p className="text-lg">No hay gastos registrados para esta campaña aún.</p>
                            <p className="text-sm mt-2">La trazabilidad se actualizará conforme se ejecuten los fondos.</p>
                        </div>
                    )}
                </motion.div>
            </div>

            <Footer />
        </main>
    );
}
