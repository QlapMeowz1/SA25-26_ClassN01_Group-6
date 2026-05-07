/**
 * Login DTO (Data Transfer Object)
 * Validates login request payload
 */

import { IsEmail, IsString } from 'class-validator';

export class LoginDTO {
  @IsEmail({}, { message: 'Email must be a valid email address' })
  email: string;

  @IsString({ message: 'Password must be a string' })
  password: string;

  constructor(partial: Partial<LoginDTO>) {
    Object.assign(this, partial);
  }
}

/**
 * Manual validation for LoginDTO
 */
export function validateLoginDTO(data: any): { valid: boolean; errors: string[] } {
  const errors: string[] = [];

  // Email validation
  if (!data.email || typeof data.email !== 'string') {
    errors.push('Email is required and must be a string');
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.push('Email must be a valid email address');
  }

  // Password validation
  if (!data.password || typeof data.password !== 'string') {
    errors.push('Password is required and must be a string');
  } else if (data.password.length === 0) {
    errors.push('Password cannot be empty');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}
