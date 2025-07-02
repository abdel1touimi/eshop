FROM node:22-alpine

# Install pnpm globally
RUN npm install -g pnpm

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json pnpm-lock.yaml ./

# Install dependencies
RUN pnpm install

# Copy source code
COPY . .

# Expose port
EXPOSE 3000

# Start the application with proper host binding
CMD ["pnpm", "dev", "--host", "0.0.0.0", "--port", "3000"]
