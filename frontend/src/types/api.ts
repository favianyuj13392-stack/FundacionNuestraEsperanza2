export interface Advertisement {
  id: number;
  title?: string;
  content?: string;
  description?: string;
  image_url?: string;
  link_url?: string;
  is_active?: boolean | number | string;
}

export interface Alliance {
  id: number;
  name?: string;
  url?: string;
  logo_url?: string;
}

export interface Stat {
  label?: string;
  number?: number | string;
}

export interface HomeSectionApi {
  identifier?: string;
  is_active?: boolean | number | string;
}
