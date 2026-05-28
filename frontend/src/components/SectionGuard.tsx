"use client";

import React, { ReactNode } from 'react';
import { useHomeSectionsContext } from '@/context/HomeSectionsContext';

interface SectionGuardProps {
  identifier: string;
  children: ReactNode;
  fallback?: ReactNode;
  hideWhileLoading?: boolean;
}

export default function SectionGuard({ identifier, children, fallback = null, hideWhileLoading = true }: SectionGuardProps) {
  const { isSectionActive, loading } = useHomeSectionsContext();

  if (loading && hideWhileLoading) {
    return null;
  }

  if (!isSectionActive(identifier)) {
    return <>{fallback}</>;
  }

  return <>{children}</>;
}
