/**
 * Register DTO (Data Transfer Object)
 * Validates registration request payload
 */

import { IsEmail, IsString, MinLength, MaxLength, Matches } from 'class-validator';

export class RegisterDTO {
  @IsEmail({}, { message: 'Email must be a valid email address' })
  email: string;

  @IsString({ message: 'Username must be a string' })
  @MinLength(3, { message: 'Username must be at least 3 characters long' })
  @MaxLength(50, { message: 'Username must be at most 50 characters long' })
  @Matches(/^[a-zA-Z0-9_-]+$/, {
    message: 'Username can only contain letters, numbers, hyphens, and underscores',
  })
  username: string;

  @IsString({ message: 'Password must be a string' })
  @MinLength(8, { message: 'Password must be at least 8 characters long' })
  @Matches(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/, {
    message: 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
  })
  password: string;

  @IsString({ message: 'Confirm password must be a string' })
  confirmPassword: string;

  constructor(partial: Partial<RegisterDTO>) {
    Object.assign(this, partial);
  }
}

/**
 * Manual validation for RegisterDTO
 * Can be used if class-validator is not available
 */
export function validateRegisterDTO(data: any): { valid: boolean; errors: string[] } {
  const errors: string[] = [];

  // Email validation
  if (!data.email || typeof data.email !== 'string') {
    errors.push('Email is required and must be a string');
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.push('Email must be a valid email address');
  }

  // Username validation
  if (!data.username || typeof data.username !== 'string') {
    errors.push('Username is required and must be a string');
  } else if (data.username.length < 3) {
    errors.push('Username must be at least 3 characters long');
  } else if (data.username.length > 50) {
    errors.push('Username must be at most 50 characters long');
  } else if (!/^[a-zA-Z0-9_-]+$/.test(data.username)) {
    errors.push('Username can only contain letters, numbers, hyphens, and underscores');
  }

  // Password validation
  if (!data.password || typeof data.password !== 'string') {
    errors.push('Password is required and must be a string');
  } else if (data.password.length < 8) {
    errors.push('Password must be at least 8 characters long');
  } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(data.password)) {
    errors.push('Password must contain at least one uppercase letter, one lowercase letter, and one number');
  }

  // Confirm password validation
  if (!data.confirmPassword) {
    errors.push('Confirm password is required');
  } else if (data.password !== data.confirmPassword) {
    errors.push('Passwords do not match');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}
