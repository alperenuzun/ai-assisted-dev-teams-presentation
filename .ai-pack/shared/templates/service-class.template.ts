import { Repository } from 'typeorm';
import { {{EntityName}} } from '../models/{{entityName}}.model';
import { AppError } from '../utils/errors';
import { PaginationOptions, PaginatedResult } from '../types/pagination';
import { logger } from '../utils/logger';

export interface {{EntityName}}CreateDTO {
  // Define creation fields
  name: string;
  // Add more fields as needed
}

export interface {{EntityName}}UpdateDTO {
  // Define update fields (all optional)
  name?: string;
  // Add more fields as needed
}

export interface {{EntityName}}FilterDTO {
  // Define filter fields
  status?: string;
  search?: string;
  // Add more filters as needed
}

/**
 * Service class for {{EntityName}} business logic
 *
 * @description
 * Handles all business logic related to {{EntityName}} entities.
 * Includes CRUD operations, validation, and business rules.
 */
export class {{EntityName}}Service {
  constructor(private readonly repository: Repository<{{EntityName}}>) {}

  /**
   * Find all {{entityNamePlural}} with pagination and filters
   *
   * @param options - Pagination options
   * @param filters - Filter criteria
   * @returns Paginated list of {{entityNamePlural}}
   */
  async findAll(
    options: PaginationOptions,
    filters?: {{EntityName}}FilterDTO
  ): Promise<PaginatedResult<{{EntityName}}>> {
    try {
      const { page = 1, limit = 20 } = options;
      const skip = (page - 1) * limit;

      const queryBuilder = this.repository.createQueryBuilder('{{entityNameCamel}}');

      // Apply filters
      if (filters?.status) {
        queryBuilder.andWhere('{{entityNameCamel}}.status = :status', {
          status: filters.status
        });
      }

      if (filters?.search) {
        queryBuilder.andWhere(
          '({{entityNameCamel}}.name ILIKE :search)',
          { search: `%${filters.search}%` }
        );
      }

      // Get total count and data
      const [data, total] = await queryBuilder
        .skip(skip)
        .take(limit)
        .orderBy('{{entityNameCamel}}.createdAt', 'DESC')
        .getManyAndCount();

      return {
        data,
        pagination: {
          page,
          limit,
          total,
          totalPages: Math.ceil(total / limit),
          hasNext: page * limit < total,
          hasPrev: page > 1
        }
      };
    } catch (error) {
      logger.error('Error finding {{entityNamePlural}}:', error);
      throw new AppError(500, 'Failed to fetch {{entityNamePlural}}', 'FETCH_ERROR');
    }
  }

  /**
   * Find {{entityName}} by ID
   *
   * @param id - {{EntityName}} ID
   * @returns {{EntityName}} entity or null
   */
  async findById(id: number): Promise<{{EntityName}} | null> {
    try {
      const {{entityNameCamel}} = await this.repository.findOne({
        where: { id }
      });

      return {{entityNameCamel}};
    } catch (error) {
      logger.error(`Error finding {{entityName}} ${id}:`, error);
      throw new AppError(500, 'Failed to fetch {{entityName}}', 'FETCH_ERROR');
    }
  }

  /**
   * Create new {{entityName}}
   *
   * @param data - {{EntityName}} creation data
   * @returns Created {{entityName}}
   */
  async create(data: {{EntityName}}CreateDTO): Promise<{{EntityName}}> {
    try {
      // Validation
      await this.validateCreate(data);

      // Create entity
      const {{entityNameCamel}} = this.repository.create(data);

      // Save to database
      const saved = await this.repository.save({{entityNameCamel}});

      logger.info(`{{EntityName}} created: ${saved.id}`);

      return saved;
    } catch (error) {
      if (error instanceof AppError) throw error;

      logger.error('Error creating {{entityName}}:', error);
      throw new AppError(500, 'Failed to create {{entityName}}', 'CREATE_ERROR');
    }
  }

  /**
   * Update existing {{entityName}}
   *
   * @param id - {{EntityName}} ID
   * @param data - Update data
   * @returns Updated {{entityName}} or null if not found
   */
  async update(
    id: number,
    data: {{EntityName}}UpdateDTO
  ): Promise<{{EntityName}} | null> {
    try {
      // Find existing
      const existing = await this.findById(id);

      if (!existing) {
        return null;
      }

      // Validation
      await this.validateUpdate(id, data);

      // Update entity
      Object.assign(existing, data);

      // Save changes
      const updated = await this.repository.save(existing);

      logger.info(`{{EntityName}} updated: ${id}`);

      return updated;
    } catch (error) {
      if (error instanceof AppError) throw error;

      logger.error(`Error updating {{entityName}} ${id}:`, error);
      throw new AppError(500, 'Failed to update {{entityName}}', 'UPDATE_ERROR');
    }
  }

  /**
   * Delete {{entityName}}
   *
   * @param id - {{EntityName}} ID
   * @returns True if deleted, false if not found
   */
  async delete(id: number): Promise<boolean> {
    try {
      // Check if exists
      const existing = await this.findById(id);

      if (!existing) {
        return false;
      }

      // Check if can be deleted
      await this.validateDelete(id);

      // Soft delete (if using soft delete)
      // await this.repository.softDelete(id);

      // Hard delete
      await this.repository.delete(id);

      logger.info(`{{EntityName}} deleted: ${id}`);

      return true;
    } catch (error) {
      if (error instanceof AppError) throw error;

      logger.error(`Error deleting {{entityName}} ${id}:`, error);
      throw new AppError(500, 'Failed to delete {{entityName}}', 'DELETE_ERROR');
    }
  }

  /**
   * Validate data for creation
   *
   * @param data - Data to validate
   * @throws AppError if validation fails
   */
  private async validateCreate(data: {{EntityName}}CreateDTO): Promise<void> {
    // Add custom validation logic

    // Example: Check for duplicates
    const existing = await this.repository.findOne({
      where: { name: data.name }
    });

    if (existing) {
      throw new AppError(
        409,
        '{{EntityName}} with this name already exists',
        'DUPLICATE_ERROR'
      );
    }
  }

  /**
   * Validate data for update
   *
   * @param id - Entity ID
   * @param data - Data to validate
   * @throws AppError if validation fails
   */
  private async validateUpdate(
    id: number,
    data: {{EntityName}}UpdateDTO
  ): Promise<void> {
    // Add custom validation logic

    // Example: Check for duplicate name if name is being updated
    if (data.name) {
      const existing = await this.repository
        .createQueryBuilder('{{entityNameCamel}}')
        .where('{{entityNameCamel}}.name = :name', { name: data.name })
        .andWhere('{{entityNameCamel}}.id != :id', { id })
        .getOne();

      if (existing) {
        throw new AppError(
          409,
          '{{EntityName}} with this name already exists',
          'DUPLICATE_ERROR'
        );
      }
    }
  }

  /**
   * Validate if entity can be deleted
   *
   * @param id - Entity ID
   * @throws AppError if deletion not allowed
   */
  private async validateDelete(id: number): Promise<void> {
    // Add custom validation logic

    // Example: Check if has dependencies
    // const hasRelatedRecords = await this.checkRelatedRecords(id);
    // if (hasRelatedRecords) {
    //   throw new AppError(
    //     409,
    //     'Cannot delete {{entityName}} with related records',
    //     'HAS_DEPENDENCIES'
    //   );
    // }
  }
}
