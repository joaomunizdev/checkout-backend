# Checkout API

Este guia descreve como executar este projeto utilizando um ambiente de desenvolvimento Docker.

## Pré-requisitos

-   [Docker](https://www.docker.com/get-started)
-   [Docker Compose](https://docs.docker.com/compose/install/)

---

## Tecnologias

-   **PHP (v8.4)**
-   **Laravel (v12)** (Eloquent, validação, rotas, container)
-   **Docker(v29.0.1^) / Docker Compose(v2.40.3^)** para ambiente de desenvolvimento
-   **OpenAPI/Swagger** via `l5-swagger`
-   **Idempotência** com `infinitypaul/laravel-idempotency`
-   **Carbon** para datas

---

## Arquitetura e Trade-offs

### Camada de Serviços

-   `CouponService` e `PaymentGatewayService` concentram a lógica de negócios.
-   **Trade-off positivo:** Controllers mais simples e código facilmente testável.
-   **Trade-off positivo:** Gateway simulado acelera desenvolvimento e pode ser trocado por um real sem alterar controllers.

### Idempotência

-   Endpoints de escrita exigem `Idempotency-Key`.
-   **Trade-off positivo:** Evita cobranças duplicadas.
-   **Trade-off negativo:** Client precisa gerar e controlar as chaves.

### Transações

-   Processos de assinatura e pagamento usam transações de banco.
-   **Trade-off positivo:** Consistência garantida — falhou, volta tudo.

### Validação de Cartão e Assinatura

-   Impede pagamento em assinaturas já ativas e reutiliza cartões armazenados.
-   **Trade-off positivo:** Evita duplicações e operações inválidas.
-   **Trade-off de simplificação:** Armazenar número completo do cartão é apenas para simulação e não é seguro para produção.

---

### Planos

-   **BASIC_MONTHLY**: R$ 49,90/mês
-   **BASIC_YEARLY**: R$ 499,00/ano
-   **PRO_MONTHLY**: R$ 99,90/mês
-   **PRO_YEARLY**: R$ 999,00/ano

### Cupons

-   **OFF10**: Concede desconto de 10% em qualquer periodicidade, sem limite de usos, sem data de expiração.
-   **SAVE30**: Concede desconto de R$30,00 no plano PRO mensal, válido por apenas 5 dias e limite de 2 usos.
-   **YEAR20**: Concede desconto de 20% nos planos anuais, válido por apenas 30 dias e limite de 5 usos.
-   **EXPIRED5**: Concede desconto de R$5,00 em qualquer periodicidade, sem limite de usos e expirado (para testar erro).

### Regras de Cálculo

-   Arredondamento sempre ao centavo _half-up_ (quando aplicável).

### Gateway Simulado

-   Regras de simulação:
    -   Aprovar transações de cartões que comecem com o número **5**.
    -   Negar transações de cartões que comecem com o número **4**.
    -   Randomizar resultado de cartões que comecem com o número **3** (70% aprovar / 30% negar).

## Testes Automatizados

-   **Unit Tests**: validam a lógica dos serviços, rotas e controllers.

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

### Rodar testes

```bash
   docker compose exec app php artisan test
```

### Refazer migrations e seeds

```bash
    docker compose exec app php artisan migrate:fresh --seed
```

### Derrubar projeto

```bash
    docker compose down -v --remove-orphans
```
