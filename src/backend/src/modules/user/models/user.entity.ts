/**
 * User Entity
 * Represents the user database model
 */

export interface IUserEntity {
  id: string;
  email: string;
  username: string;
  password_hash: string;
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
 * User Entity Class
 * Used for TypeORM/Sequelize ORM mapping
 */
export class UserEntity implements IUserEntity {
  id: string;
  email: string;
  username: string;
  password_hash: string;
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

  constructor(data: Partial<IUserEntity>) {
    this.id = data.id || '';
    this.email = data.email || '';
    this.username = data.username || '';
    this.password_hash = data.password_hash || '';
    this.first_name = data.first_name || null;
    this.last_name = data.last_name || null;
    this.bio = data.bio || null;
    this.avatar_url = data.avatar_url || null;
    this.phone = data.phone || null;
    this.location = data.location || null;
    this.rating = data.rating || 1200;
    this.wins = data.wins || 0;
    this.losses = data.losses || 0;
    this.is_active = data.is_active ?? true;
    this.is_verified = data.is_verified ?? false;
    this.role = data.role || 'user';
    this.followers_count = data.followers_count || 0;
    this.following_count = data.following_count || 0;
    this.created_at = data.created_at || new Date();
    this.updated_at = data.updated_at || new Date();
    this.last_login_at = data.last_login_at || null;
  }
}
