"use client";
import React from 'react';

interface ProgramsHeroProps {
    title: string;
    subtitle: string;
}

const ProgramsHero: React.FC<ProgramsHeroProps> = ({ title, subtitle }) => {
    return (
        <section className="bg-celeste-claro py-20 text-center">
            <div className="container mx-auto px-6">
                <h1 className="text-4xl md:text-5xl font-bold text-azul-marino font-title mb-6">
                    {title}
                </h1>
                <p className="text-xl text-gray-700 max-w-3xl mx-auto font-sans">
                    {subtitle}
                </p>
            </div>
        </section>
    );
};

export default ProgramsHero;