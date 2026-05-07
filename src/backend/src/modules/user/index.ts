/**
 * User Module Index
 * Exports all user module components
 */

export { UserController } from './controllers/user.controller';
export { UserEntity, type IUserEntity } from './models/user.entity';
export { type IUser, type IUserProfile, type IAuthPayload, type ILoginResponse } from './models/user.interface';
export { RegisterDTO, validateRegisterDTO } from './dto/register.dto';
export { LoginDTO, validateLoginDTO } from './dto/login.dto';
export { UpdateProfileDTO, validateUpdateProfileDTO } from './dto/update-profile.dto';
export { UserRepository } from './repositories/user.repository';
export { UserService } from './services/user.service';
export { default as userRoutes } from './routes/user.routes';
