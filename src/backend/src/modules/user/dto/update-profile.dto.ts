/**
 * Update Profile DTO (Data Transfer Object)
 * Validates profile update request payload
 */

import { IsString, IsOptional, MaxLength, IsEmail } from 'class-validator';

export class UpdateProfileDTO {
  @IsOptional()
  @IsString({ message: 'First name must be a string' })
  @MaxLength(100, { message: 'First name must be at most 100 characters' })
  first_name?: string;

  @IsOptional()
  @IsString({ message: 'Last name must be a string' })
  @MaxLength(100, { message: 'Last name must be at most 100 characters' })
  last_name?: string;

  @IsOptional()
  @IsString({ message: 'Bio must be a string' })
  @MaxLength(500, { message: 'Bio must be at most 500 characters' })
  bio?: string;

  @IsOptional()
  @IsString({ message: 'Avatar URL must be a string' })
  @MaxLength(500, { message: 'Avatar URL must be at most 500 characters' })
  avatar_url?: string;

  @IsOptional()
  @IsString({ message: 'Phone must be a string' })
  @MaxLength(20, { message: 'Phone must be at most 20 characters' })
  phone?: string;

  @IsOptional()
  @IsString({ message: 'Location must be a string' })
  @MaxLength(255, { message: 'Location must be at most 255 characters' })
  location?: string;

  constructor(partial: Partial<UpdateProfileDTO>) {
    Object.assign(this, partial);
  }
}

/**
 * Manual validation for UpdateProfileDTO
 */
export function validateUpdateProfileDTO(data: any): { valid: boolean; errors: string[] } {
  const errors: string[] = [];

  // First name validation
  if (data.first_name !== undefined) {
    if (typeof data.first_name !== 'string') {
      errors.push('First name must be a string');
    } else if (data.first_name.length > 100) {
      errors.push('First name must be at most 100 characters');
    }
  }

  // Last name validation
  if (data.last_name !== undefined) {
    if (typeof data.last_name !== 'string') {
      errors.push('Last name must be a string');
    } else if (data.last_name.length > 100) {
      errors.push('Last name must be at most 100 characters');
    }
  }

  // Bio validation
  if (data.bio !== undefined) {
    if (typeof data.bio !== 'string') {
      errors.push('Bio must be a string');
    } else if (data.bio.length > 500) {
      errors.push('Bio must be at most 500 characters');
    }
  }

  // Avatar URL validation
  if (data.avatar_url !== undefined) {
    if (typeof data.avatar_url !== 'string') {
      errors.push('Avatar URL must be a string');
    } else if (data.avatar_url.length > 500) {
      errors.push('Avatar URL must be at most 500 characters');
    }
  }

  // Phone validation
  if (data.phone !== undefined) {
    if (typeof data.phone !== 'string') {
      errors.push('Phone must be a string');
    } else if (data.phone.length > 20) {
      errors.push('Phone must be at most 20 characters');
    }
  }

  // Location validation
  if (data.location !== undefined) {
    if (typeof data.location !== 'string') {
      errors.push('Location must be a string');
    } else if (data.location.length > 255) {
      errors.push('Location must be at most 255 characters');
    }
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}
