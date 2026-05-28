"use client";
import React from 'react';

interface TestimonialsHeroProps {
    title: string;
    subtitle: string;
    image: string | null;
}

const TestimonialsHero: React.FC<TestimonialsHeroProps> = ({ title, subtitle, image }) => {
    const bgStyle = image 
        ? { backgroundImage: `url(${image})`, backgroundSize: 'cover', backgroundPosition: 'center' } 
        : undefined;

    return (
        <section className="bg-celeste-claro py-20 text-center" style={bgStyle}>
            <div className={image ? 'bg-black/40 py-20' : 'py-20'}>
                <div className="container mx-auto px-6">
                    <h1 className={`text-4xl md:text-5xl font-bold font-title mb-6 ${image ? 'text-white' : 'text-azul-marino'}`}>
                        {title}
                    </h1>
                    <p className={`text-xl max-w-3xl mx-auto font-sans ${image ? 'text-gray-200' : 'text-gray-700'}`}>
                        {subtitle}
                    </p>
                </div>
            </div>
        </section>
    );
};

export default TestimonialsHero;