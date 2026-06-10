"use client";

import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { API_BASE_URL } from '@/utils/apiBaseUrl';

export interface HomeSectionStatus {
  identifier: string;
  is_active: boolean;
}

interface HomeSectionsContextValue {
  sections: HomeSectionStatus[];
  loading: boolean;
  error: string | null;
  isSectionActive: (identifier: string) => boolean;
}

const HomeSectionsContext = createContext<HomeSectionsContextValue | undefined>(undefined);

export function HomeSectionsProvider({ children }: { children: ReactNode }) {
  const [sections, setSections] = useState<HomeSectionStatus[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadSections = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/api/section-statuses`);
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        const normalized = Array.isArray(data)
          ? (data as Record<string, unknown>[]).map((item) => ({
              identifier: String(item['identifier'] || ''),
              is_active: Boolean(item['is_active']),
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

  return (
    <HomeSectionsContext.Provider value={{ sections, loading, error, isSectionActive }}>
      {children}
    </HomeSectionsContext.Provider>
  );
}

export function useHomeSectionsContext() {
  const context = useContext(HomeSectionsContext);
  if (!context) {
    throw new Error('useHomeSectionsContext must be used within HomeSectionsProvider');
  }
  return context;
}
