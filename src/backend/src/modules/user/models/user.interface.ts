/**
 * User Interfaces
 * Define TypeScript types and interfaces for the User module
 */

export interface IUser {
  id: string;
  email: string;
  username: string;
  first_name: string | null;
  last_name: string | null;
  bio: string | null;
  avatar_url: string | null;
  phone: string | null;
  location: string | null;
  rating: number;
  wins: number;
  losses: number;
  is_active: boolean;
  is_verified: boolean;
  role: 'user' | 'admin' | 'moderator';
  followers_count: number;
  following_count: number;
  created_at: Date;
  updated_at: Date;
  last_login_at: Date | null;
}

/**
 * User without sensitive fields
 * Safe to return to client
 */
export interface IUserProfile extends Omit<IUser, 'role'> {
  win_rate?: number;
}

/**
 * User authentication payload
 */
export interface IAuthPayload {
  userId: string;
  email: string;
  username: string;
  role: 'user' | 'admin' | 'moderator';
}

/**
 * JWT payload
 */
export interface IJWTPayload {
  userId: string;
  email: string;
  username: string;
  role: 'user' | 'admin' | 'moderator';
  iat?: number;
  exp?: number;
}

/**
 * Login response
 */
export interface ILoginResponse {
  accessToken: string;
  user: IUserProfile;
}

/**
 * Register response
 */
export interface IRegisterResponse {
  user: IUserProfile;
  message: string;
}
