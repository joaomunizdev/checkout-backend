# Checkout API

Este guia descreve como executar este projeto utilizando um ambiente de desenvolvimento Docker.

## Pré-requisitos

-   [Docker](https://www.docker.com/get-started)
-   [Docker Compose](https://docs.docker.com/compose/install/)

---

## Guia de Instalação e Execução

Siga estes passos para configurar e levantar o ambiente de desenvolvimento.

### 1. Configurar o Arquivo de Ambiente (.env)

```bash
    cp .env.example .env
```

### 2. Construir e Subir os Contêineres

Com o Docker em execução, execute o seguinte comando na raiz do projeto:

```bash
    docker compose up -d --build
```

### 3. Instalar Dependências do PHP (Composer)

```bash
    docker compose exec app composer install
```

### 4. Gerar a Chave da Aplicação

```bash
    docker compose exec app php artisan key:generate
```

### 5. Executar as Migrations

```bash
    docker compose exec app php artisan migrate
```

### 6. Executar as Seeds

```bash
    docker compose exec app php artisan db:seed
```

### 6. Acesso ao projeto

```bash
    http://localhost:8000
```

### 7. Acesso a documentação da API

```bash
    http://localhost:8000/api/documentation
```

### Refazer migrations e seeds

```bash
    docker compose exec app php artisan migrate:fresh --seed
```

### Derrubar projeto

```bash
    docker compose down -v --remove-orphans
```
