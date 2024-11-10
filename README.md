# Model-agnostic Retrieval Augmented Generation System

This project provides a straightforward API service to test a Retrieval-Augmented Generation (RAG) system using Large Language Models (LLMs), with the flexibility to include contextual documents or operate without them. Designed specifically for research and experimental testing, this API is not intended for production environments. 

## Documents
To represent the context, we use this diagram with a PostgreSQL database, on which the **pgvector** extension must be installed. We assume that you already have a configured database.

```sql
DROP TABLE IF EXISTS "document";
DROP SEQUENCE IF EXISTS document_id_seq;
CREATE SEQUENCE document_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."document" (
    "id" integer DEFAULT nextval('document_id_seq') NOT NULL,
    "created_at" timestamp(0) NOT NULL,
    "embedding" vector(1024) NOT NULL,
    "content" text NOT NULL,
    "source_type" text NOT NULL,
    "source_name" text NOT NULL,
    CONSTRAINT "document_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

COMMENT ON COLUMN "public"."document"."created_at" IS '(DC2Type:datetime_immutable)';
```

You can import your context with the following command
```shell
php bin/console doctrine:datebase:create --if-not-exists
php bin/console doctrine:migration:migrate
php bin/console doctrine:query:sql "$(< dataset/document.sql)"
```

## Embeddings

**Request**
```shell
curl --request POST \
  --url http://localhost:8080/v1/embeddings \
  --header 'Content-Type: application/json' \
  --data '{
	"text": "parle moi de la loi du numérique en droit congolais",
	"dimension": 512,
	"embeddingsModel": {
		"provider": "mistral",
		"model": "mistral-embed"
	}
}
'
```

**Response**
```json
{
  "embeddings": [
    -0.027252197265625,
    0.001789093017578125,
    0.0261383056640625,
    -0.0160980224609375
  ],
  "createdAt": "1731197428",
  "benchmark": {
    "duration": 1602.6,
    "memory": 10485760
  },
  "embeddingsModel": {
    "provider": "mistral",
    "model": "mistral-embed"
  }
}
```

## Completion
You can choose to generate text with or without context.
The list of available models and providers is configurable and can be extended to meet specific requirements.

| provider | Model                           |
|----------|---------------------------------|
| google   | gemini-1.5-pro                  |
| openai   | gpt-4-turbo, gpt-3.5-turbo-0125 |
| ollama   | vicuna, mistral-7b, lama2-7b    |
| mistral  | mistral-large-2407              |

**Request**
```shell
curl --request POST \
  --url http://localhost:8080/v1/chats/completion \
  --header 'Content-Type: application/json' \
  --data '{
	"prompt": "parle moi de la loi du numérique en droit congolais",
	"embeddingsModel": {
		"provider": "mistral",
		"model": "mistral-embed"
	},
	"generativeModel": {
		"provider": "google",
		"model": "gemini-1.5-pro"
	},
	"useContext": true
}
'
```

**Response**
```json
{
  "response": "La loi sur les transactions électroniques...",
  "createdAt": "1731196808",
  "benchmark": {
    "duration": 10288.7,
    "memory": 10485760
  },
  "generativeModel": {
    "provider": "google",
    "model": "gemini-1.5-pro"
  },
  "embeddingsModel": {
    "provider": "mistral",
    "model": "mistral-embed"
  }
}
```

