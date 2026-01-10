import React, { useState, useEffect, useCallback } from 'react';
import styles from './{{ComponentName}}.module.css';

export interface {{ComponentName}}Props {
  /**
   * Unique identifier for the component
   */
  id?: string;

  /**
   * CSS class name for custom styling
   */
  className?: string;

  /**
   * Callback function triggered on action
   */
  onAction?: (data: any) => void;

  /**
   * Data to display in the component
   */
  data?: any;

  /**
   * Loading state
   */
  isLoading?: boolean;

  /**
   * Error message to display
   */
  error?: string | null;
}

/**
 * {{ComponentName}} component
 *
 * @description
 * [Detailed description of what this component does]
 *
 * @example
 * ```tsx
 * <{{ComponentName}}
 *   data={myData}
 *   onAction={handleAction}
 *   isLoading={false}
 * />
 * ```
 */
export const {{ComponentName}}: React.FC<{{ComponentName}}Props> = ({
  id,
  className,
  onAction,
  data,
  isLoading = false,
  error = null,
}) => {
  // State management
  const [localState, setLocalState] = useState<any>(null);

  // Effects
  useEffect(() => {
    // Component initialization logic
    if (data) {
      setLocalState(data);
    }
  }, [data]);

  // Callbacks
  const handleAction = useCallback(() => {
    if (onAction) {
      onAction(localState);
    }
  }, [onAction, localState]);

  // Render helpers
  const renderContent = () => {
    if (isLoading) {
      return (
        <div className={styles.loading}>
          <span>Loading...</span>
        </div>
      );
    }

    if (error) {
      return (
        <div className={styles.error} role="alert">
          <span>{error}</span>
        </div>
      );
    }

    return (
      <div className={styles.content}>
        {/* Main content here */}
        <p>Component content</p>
      </div>
    );
  };

  // Main render
  return (
    <div
      id={id}
      className={`${styles.container} ${className || ''}`}
      data-testid="{{componentName}}"
      role="region"
      aria-label="{{ComponentName}}"
    >
      {renderContent()}
    </div>
  );
};

{{ComponentName}}.displayName = '{{ComponentName}}';

export default {{ComponentName}};
