import { Router, Request, Response, NextFunction } from 'express';
import { validate } from '../middleware/validation';
import { authenticate, authorize } from '../middleware/auth';
import { {{serviceName}}Service } from '../services/{{serviceName}}.service';
import { {{entityName}}Schema } from '../schemas/{{entityName}}.schema';
import { AppError } from '../utils/errors';

const router = Router();
const service = new {{serviceName}}Service();

/**
 * @swagger
 * /api/v1/{{routePath}}:
 *   get:
 *     summary: Get all {{entityNamePlural}}
 *     tags: [{{entityName}}]
 *     security:
 *       - bearerAuth: []
 *     parameters:
 *       - in: query
 *         name: page
 *         schema:
 *           type: integer
 *         description: Page number
 *       - in: query
 *         name: limit
 *         schema:
 *           type: integer
 *         description: Items per page
 *     responses:
 *       200:
 *         description: List of {{entityNamePlural}}
 *       401:
 *         description: Unauthorized
 */
router.get('/',
  authenticate,
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const { page = 1, limit = 20, ...filters } = req.query;

      const result = await service.findAll({
        page: Number(page),
        limit: Number(limit),
        filters
      });

      res.status(200).json({
        success: true,
        data: result.data,
        pagination: result.pagination
      });
    } catch (error) {
      next(error);
    }
  }
);

/**
 * @swagger
 * /api/v1/{{routePath}}/{id}:
 *   get:
 *     summary: Get {{entityName}} by ID
 *     tags: [{{entityName}}]
 *     security:
 *       - bearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       200:
 *         description: {{entityName}} details
 *       404:
 *         description: {{entityName}} not found
 */
router.get('/:id',
  authenticate,
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const { id } = req.params;

      const result = await service.findById(Number(id));

      if (!result) {
        throw new AppError(404, '{{entityName}} not found', 'NOT_FOUND');
      }

      res.status(200).json({
        success: true,
        data: result
      });
    } catch (error) {
      next(error);
    }
  }
);

/**
 * @swagger
 * /api/v1/{{routePath}}:
 *   post:
 *     summary: Create new {{entityName}}
 *     tags: [{{entityName}}]
 *     security:
 *       - bearerAuth: []
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/{{entityName}}'
 *     responses:
 *       201:
 *         description: {{entityName}} created successfully
 *       400:
 *         description: Invalid input
 *       422:
 *         description: Validation failed
 */
router.post('/',
  authenticate,
  validate({{entityName}}Schema.create),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const result = await service.create(req.body);

      res.status(201).json({
        success: true,
        data: result,
        message: '{{entityName}} created successfully'
      });
    } catch (error) {
      next(error);
    }
  }
);

/**
 * @swagger
 * /api/v1/{{routePath}}/{id}:
 *   put:
 *     summary: Update {{entityName}}
 *     tags: [{{entityName}}]
 *     security:
 *       - bearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             $ref: '#/components/schemas/{{entityName}}'
 *     responses:
 *       200:
 *         description: {{entityName}} updated successfully
 *       404:
 *         description: {{entityName}} not found
 */
router.put('/:id',
  authenticate,
  validate({{entityName}}Schema.update),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const { id } = req.params;

      const result = await service.update(Number(id), req.body);

      if (!result) {
        throw new AppError(404, '{{entityName}} not found', 'NOT_FOUND');
      }

      res.status(200).json({
        success: true,
        data: result,
        message: '{{entityName}} updated successfully'
      });
    } catch (error) {
      next(error);
    }
  }
);

/**
 * @swagger
 * /api/v1/{{routePath}}/{id}:
 *   delete:
 *     summary: Delete {{entityName}}
 *     tags: [{{entityName}}]
 *     security:
 *       - bearerAuth: []
 *     parameters:
 *       - in: path
 *         name: id
 *         required: true
 *         schema:
 *           type: integer
 *     responses:
 *       204:
 *         description: {{entityName}} deleted successfully
 *       404:
 *         description: {{entityName}} not found
 */
router.delete('/:id',
  authenticate,
  authorize(['admin']),
  async (req: Request, res: Response, next: NextFunction) => {
    try {
      const { id } = req.params;

      const result = await service.delete(Number(id));

      if (!result) {
        throw new AppError(404, '{{entityName}} not found', 'NOT_FOUND');
      }

      res.status(204).send();
    } catch (error) {
      next(error);
    }
  }
);

export default router;
