<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Control de Riego') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Dashboard Oscuro -->
            <div style="font-family: 'Segoe UI', system-ui, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; border-radius: 12px;">
                
                <div id="connection-status" style="display: inline-block; padding: 8px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; background: #ef4444; color: white; margin-bottom: 20px;">Desconectado</div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    
                    <!-- Temperatura -->
                    <div style="background: #1e293b; border-radius: 16px; padding: 25px; text-align: center; border: 1px solid #334155;">
                        <h2 style="font-size: 1rem; color: #94a3b8; margin: 0 0 15px 0; text-transform: uppercase;">Temperatura</h2>
                        <div id="temperatura" style="font-size: 2.5rem; font-weight: 700; margin: 10px 0;">-- °C</div>
                        <div id="temp-status" style="padding: 6px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; background: rgba(148, 163, 184, 0.1); color: #94a3b8;">Normal</div>
                    </div>

                    <!-- Humedad -->
                    <div style="background: #1e293b; border-radius: 16px; padding: 25px; text-align: center; border: 1px solid #334155;">
                        <h2 style="font-size: 1rem; color: #94a3b8; margin: 0 0 15px 0; text-transform: uppercase;">Humedad Tierra</h2>
                        <div id="humedad" style="font-size: 2.5rem; font-weight: 700; margin: 10px 0;">-- %</div>
                    </div>

                    <!-- Peso -->
                    <div style="background: #1e293b; border-radius: 16px; padding: 25px; text-align: center; border: 1px solid #334155;">
                        <h2 style="font-size: 1rem; color: #94a3b8; margin: 0 0 15px 0; text-transform: uppercase;">Peso Tanque</h2>
                        <div id="peso" style="font-size: 2.5rem; font-weight: 700; margin: 10px 0;">-- g</div>
                        <div id="peso-status" style="padding: 6px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; background: rgba(16, 185, 129, 0.2); color: #10b981;">Suficiente</div>
                    </div>

                    <!-- Bomba -->
                    <div style="background: #1e293b; border-radius: 16px; padding: 25px; text-align: center; border: 1px solid #334155;">
                        <h2 style="font-size: 1rem; color: #94a3b8; margin: 0 0 15px 0; text-transform: uppercase;">Bomba</h2>
                        <div id="bomba" style="font-size: 2rem; font-weight: 700; margin: 15px 0;">--</div>
                        <div id="bomba-status" style="padding: 6px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; background: rgba(148, 163, 184, 0.1); color: #94a3b8;">Apagada</div>
                    </div>
                </div>

                <!-- Botón de Control Manual (Movido fuera de la cuadrícula para que se vea mejor) -->
                <div style="text-align: center; margin-top: 30px;">
                    <button id="btn-bomba" onclick="toggleBomba()" style="background: #3b82f6; color: white; padding: 12px 25px; border: none; border-radius: 8px; font-weight: bold; font-size: 1rem; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
                        Cargando...
                    </button>
                </div>

            </div>

        </div>
    </div>

    <script>
    // 1. Función para actualizar los datos en tiempo real
    function actualizar() {
        fetch('/api/arduino/latest')
            .then(res => res.json())
            .then(data => {
                const connStatus = document.getElementById('connection-status');
                if (!data || data.id === undefined) return;

                connStatus.textContent = 'En Vivo';
                connStatus.style.background = '#10b981';

                document.getElementById('temperatura').textContent = (data.temperatura ? data.temperatura.toFixed(1) : '--') + ' °C';
                const tempBadge = document.getElementById('temp-status');
                if (data.alerta_termica == 1 || data.alerta_termica === true) {
                    tempBadge.textContent = '¡Peligro!';
                    tempBadge.style.background = 'rgba(239, 68, 68, 0.2)';
                    tempBadge.style.color = '#ef4444';
                } else {
                    tempBadge.textContent = 'Normal';
                    tempBadge.style.background = 'rgba(16, 185, 129, 0.2)';
                    tempBadge.style.color = '#10b981';
                }

                document.getElementById('humedad').textContent = (data.humedad ? data.humedad.toFixed(0) : '--') + ' %';

                document.getElementById('peso').textContent = (data.peso ? data.peso.toFixed(1) : '--') + ' g';
                const pesoBadge = document.getElementById('peso-status');
                if (data.alerta_peso == 1 || data.alerta_peso === true) {
                    pesoBadge.textContent = '¡Llenar!';
                    pesoBadge.style.background = 'rgba(239, 68, 68, 0.2)';
                    pesoBadge.style.color = '#ef4444';
                } else {
                    pesoBadge.textContent = 'Suficiente';
                    pesoBadge.style.background = 'rgba(16, 185, 129, 0.2)';
                    pesoBadge.style.color = '#10b981';
                }

                // --- LÓGICA DE LA BOMBA CORREGIDA ---
                const bombaVal = document.getElementById('bomba');
                const bombaBadge = document.getElementById('bomba-status');
                
                // Convertimos el dato que llega (1 o true) a un booleano real
                const estaEncendida = (data.bomba_encendida == 1 || data.bomba_encendida === true);

                if (estaEncendida) {
                    bombaVal.textContent = 'ON';
                    bombaVal.style.color = '#10b981'; // Verde
                    bombaBadge.textContent = 'Encendida';
                    bombaBadge.style.background = 'rgba(16, 185, 129, 0.2)';
                    bombaBadge.style.color = '#10b981';
                } else {
                    bombaVal.textContent = 'OFF';
                    bombaVal.style.color = '#ef4444'; // Rojo
                    bombaBadge.textContent = 'Apagada';
                    bombaBadge.style.background = 'rgba(148, 163, 184, 0.1)';
                    bombaBadge.style.color = '#94a3b8';
                }
            })
            .catch(() => {
                document.getElementById('connection-status').textContent = 'Desconectado';
                document.getElementById('connection-status').style.background = '#ef4444';
            });
    }

    // 2. Función para encender/apagar desde la web (Botón)
    function toggleBomba() {
        fetch('/bomba/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            // Convertimos el estado del botón a booleano
            const nuevoEstado = (data.estado == 1 || data.estado === true);
            actualizarBoton(nuevoEstado);
        });
    }

    // 3. Función para cambiar el color y texto del botón
    function actualizarBoton(encendido) {
        const btn = document.getElementById('btn-bomba');
        if (!btn) return;

        if (encendido) {
            btn.textContent = 'Apagar Bomba (Manual)';
            btn.style.background = '#ef4444'; // Rojo
        } else {
            btn.textContent = 'Encender Bomba (Manual)';
            btn.style.background = '#3b82f6'; // Azul
        }
    }

    // 4. Inicialización
    actualizar();
    setInterval(actualizar, 3000);

    // Pintamos el botón con su estado real al entrar a la página
    fetch('/api/arduino/bomba-estado')
        .then(res => res.json())
        .then(data => {
            const estadoReal = (data.bomba_manual == 1 || data.bomba_manual === true);
            actualizarBoton(estadoReal);
        })
        .catch(err => console.error("Error al cargar estado del botón:", err));
    </script>
</x-app-layout>