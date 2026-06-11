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

export function usePageSection(identifier: string) {
  const [section, setSection] = useState<PageSectionData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const loadSection = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}/api/sections/${identifier}`);

        if (!response.ok) {
          if (response.status === 404) {
            setSection(null);
            return;
          }

          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        setSection(data as PageSectionData);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Error loading section');
      } finally {
        setLoading(false);
      }
    };

    loadSection();
  }, [identifier]);

  return {
    section,
    loading,
    error,
  };
}
