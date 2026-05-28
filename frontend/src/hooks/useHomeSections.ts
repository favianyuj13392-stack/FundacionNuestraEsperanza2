"use client";

import { useEffect, useState } from 'react';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL ?? 'http://127.0.0.1:8000';

export interface HomeSectionStatus {
  identifier: string;
  is_active: boolean;
}

export function useHomeSections() {
  const [sections, setSections] = useState<HomeSectionStatus[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadSections = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/api/home-sections`);
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        const normalized = Array.isArray(data)
          ? data.map((item: any) => ({
              identifier: item.identifier,
              is_active: Boolean(item.is_active),
            }))
          : [];

        setSections(normalized);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Error loading home sections');
      } finally {
        setLoading(false);
      }
    };

    loadSections();
  }, []);

  const isSectionActive = (identifier: string) => {
    return sections.some((section) => section.identifier === identifier && section.is_active);
  };

  return {
    sections,
    loading,
    error,
    isSectionActive,
  };
}
