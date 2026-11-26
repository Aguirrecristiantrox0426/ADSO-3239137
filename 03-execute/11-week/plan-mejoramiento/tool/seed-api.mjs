
const baseUrl =
  process.env.RENT_TOOLS_API_URL ??
  'http://localhost:8000/api/v1';

const email =
  process.env.RENT_TOOLS_ADMIN_EMAIL ??
  process.env.DEMO_ADMIN_EMAIL;

const password =
  process.env.RENT_TOOLS_ADMIN_PASSWORD ??
  process.env.DEMO_ADMIN_PASSWORD;

if (!email || !password) {
  throw new Error(
    'Configure RENT_TOOLS_ADMIN_EMAIL y RENT_TOOLS_ADMIN_PASSWORD.'
  );
}

async function request(path, options = {}, token) {
  const response = await fetch(`${baseUrl}${path}`, {
    ...options,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token
        ? { Authorization: `Bearer ${token}` }
        : {}),
      ...options.headers,
    },
  });

  const body = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error(
      `${options.method ?? 'GET'} ${path} (${response.status}): ${
        body?.message ?? response.statusText
      }`
    );
  }

  return body;
}

async function main() {
  // 1. Autenticación
  const login = await request('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  });

  const token = login.data?.token;

  if (!token) {
    throw new Error('No se recibió el token de autenticación.');
  }

  console.log('Administrador autenticado correctamente.');

  // 2. Crear categorías faltantes
  const categoryData = [
    {
      name: 'Herramientas eléctricas',
      description: 'Equipos eléctricos para construcción y mantenimiento.',
    },
    {
      name: 'Herramientas manuales',
      description: 'Herramientas para trabajos manuales.',
    },
    {
      name: 'Equipos de corte',
      description: 'Equipos para corte y acabado de materiales.',
    },
  ];

  let categories = (await request('/categories', {}, token)).data ?? [];

  for (const category of categoryData) {
    let existing = categories.find(
      item => item.name.toLowerCase() === category.name.toLowerCase()
    );

    if (!existing) {
      const result = await request(
        '/categories',
        {
          method: 'POST',
          body: JSON.stringify(category),
        },
        token
      );

      existing = result.data;
      categories.push(existing);

      console.log(`Categoría creada: ${category.name}`);
    } else {
      console.log(`Categoría existente: ${category.name}`);
    }
  }

  const categoryIds = new Map(
    categories.map(category => [category.name, category.id])
  );

  // 3. Crear herramientas faltantes
  const toolData = [
    {
      name: 'Rotomartillo SDS',
      description: 'Equipo para perforación de concreto.',
      total_quantity: 5,
      category_id: categoryIds.get('Herramientas eléctricas'),
    },
    {
      name: 'Lijadora orbital',
      description: 'Equipo eléctrico para lijado de superficies.',
      total_quantity: 4,
      category_id: categoryIds.get('Herramientas eléctricas'),
    },
    {
      name: 'Juego de llaves combinadas',
      description: 'Juego de herramientas manuales para mantenimiento.',
      total_quantity: 10,
      category_id: categoryIds.get('Herramientas manuales'),
    },
    {
      name: 'Cortadora de cerámica',
      description: 'Equipo para corte de piezas cerámicas.',
      total_quantity: 3,
      category_id: categoryIds.get('Equipos de corte'),
    },
  ];

  const existingTools = (await request('/tools', {}, token)).data ?? [];

  for (const tool of toolData) {
    const exists = existingTools.some(
      item => item.name.toLowerCase() === tool.name.toLowerCase()
    );

    if (exists) {
      console.log(`Herramienta existente: ${tool.name}`);
      continue;
    }

    await request(
      '/tools',
      {
        method: 'POST',
        body: JSON.stringify(tool),
      },
      token
    );

    console.log(`Herramienta creada: ${tool.name}`);
  }

  // 4. Crear clientes faltantes
  const customerData = [
    {
      full_name: 'Andrés Felipe Gómez',
      identification_number: '9000000101',
      phone: '3101234567',
      email: 'andres.gomez@example.com',
    },
    {
      full_name: 'Daniela Marcela Rodríguez',
      identification_number: '9000000102',
      phone: '3107654321',
      email: 'daniela.rodriguez@example.com',
    },
  ];

  const existingCustomers = (await request('/customers', {}, token)).data ?? [];

  for (const customer of customerData) {
    const exists = existingCustomers.some(
      item =>
        item.identification_number === customer.identification_number
    );

    if (exists) {
      console.log(
        `Cliente existente: ${customer.identification_number}`
      );
      continue;
    }

    await request(
      '/customers',
      {
        method: 'POST',
        body: JSON.stringify(customer),
      },
      token
    );

    console.log(`Cliente creado: ${customer.full_name}`);
  }

  console.log('Sembrado mediante API finalizado correctamente.');
}

main().catch(error => {
  console.error('Error durante el sembrado:', error.message);
  process.exitCode = 1;
});
