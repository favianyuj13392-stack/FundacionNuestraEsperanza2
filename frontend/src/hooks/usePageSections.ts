"use client";

import { useEffect, useState } from 'react';
import { API_BASE_URL } from '@/utils/apiBaseUrl';

export interface PageSectionData {
  id: number;
  identifier: string;
  name: string;
  title?: string;
  subtitle?: string;
  content?: string;
  image?: string | null;
  is_active?: boolean;
  order?: number;
  meta_title?: string;
  meta_description?: string;
  meta_keywords?: string;
}

export function usePageSections() {
  const [sections, setSections] = useState<PageSectionData[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadSections = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/api/how-to-help-sections`);

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        setSections(Array.isArray(data) ? data : []);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Error loading page sections');
      } finally {
        setLoading(false);
      }
    };

    loadSections();
  }, []);

  return {
    sections,
    loading,
    error,
  };
}
