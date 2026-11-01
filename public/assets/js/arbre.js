(function () {
    'use strict'

    document.querySelectorAll('[data-tree-action]').forEach(function (button) {
        button.addEventListener('click', function () {
            const open = button.dataset.treeAction === 'expand'
            document.querySelectorAll('.person-tree details').forEach(function (details) {
                details.open = open
            })
        })
    })

    const dataElement = document.getElementById('tree-data')
    const switchButtons = Array.from(document.querySelectorAll('[data-tree-view-button]'))
    if (!dataElement || switchButtons.length === 0) return

    let graph
    try {
        graph = JSON.parse(dataElement.textContent || '{}')
    } catch (error) {
        return
    }

    if (!Array.isArray(graph.nodes) || graph.nodes.length === 0) return

    const panels = Array.from(document.querySelectorAll('[data-tree-view-panel]'))
    const graphToolbar = document.querySelector('[data-graph-toolbar]')
    const graphHint = document.querySelector('[data-graph-hint]')
    const graphCaption = document.querySelector('[data-graph-caption]')
    const zoomButtons = Array.from(document.querySelectorAll('[data-tree-zoom]'))
    const rotateButton = document.querySelector('[data-tree-rotate]')
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)')

    const layout = buildLayout(graph)
    const renderers = {}
    let activeView = 'standard'
    let activeRenderer = null

    function buildLayout(source) {
        const width = 166
        const height = 52
        const gapX = 20
        const gapY = 76
        const nodes = source.nodes.map(function (node) {
            return Object.assign({}, node, { childrenIds: [] })
        })
        const byId = new Map(nodes.map(function (node) { return [String(node.id), node] }))
        const childrenByParent = new Map()

        source.edges.forEach(function (edge) {
            const sourceId = String(edge.source)
            const targetId = String(edge.target)
            if (!byId.has(sourceId) || !byId.has(targetId)) return
            if (!childrenByParent.has(sourceId)) childrenByParent.set(sourceId, [])
            childrenByParent.get(sourceId).push(targetId)
            byId.get(sourceId).childrenIds.push(targetId)
        })

        childrenByParent.forEach(function (children) {
            children.sort(function (a, b) {
                const nodeA = byId.get(a)
                const nodeB = byId.get(b)
                if (nodeA.generation !== nodeB.generation) return nodeA.generation - nodeB.generation
                return a.localeCompare(b, undefined, { numeric: true })
            })
        })

        const positions = new Map()
        let cursor = 0

        function place(id, depth, seen) {
            if (!byId.has(id) || seen.has(id)) return null
            seen.add(id)

            const node = byId.get(id)
            const children = node.childrenIds.filter(function (childId) { return !seen.has(childId) })
            let centerX

            if (children.length === 0) {
                centerX = cursor + width / 2
                cursor += width + gapX
            } else {
                const childCenters = []
                children.forEach(function (childId) {
                    const childCenter = place(childId, depth + 1, seen)
                    if (childCenter !== null) childCenters.push(childCenter)
                })

                if (childCenters.length === 0) {
                    centerX = cursor + width / 2
                    cursor += width + gapX
                } else {
                    centerX = (childCenters[0] + childCenters[childCenters.length - 1]) / 2
                }
            }

            positions.set(id, {
                x: centerX,
                y: depth * (height + gapY) + height / 2,
                depth: depth
            })

            return centerX
        }

        const rootId = source.root !== null && byId.has(String(source.root))
            ? String(source.root)
            : String(nodes[0].id)
        place(rootId, 0, new Set())

        nodes.forEach(function (node) {
            if (!positions.has(String(node.id))) {
                place(String(node.id), 0, new Set())
            }
        })

        nodes.forEach(function (node) {
            const position = positions.get(String(node.id))
            node.x = position.x - width / 2
            node.y = position.y
            node.centerX = position.x
            node.depth = position.depth
            node.width = width
            node.height = height
        })

        let minX = Infinity
        let minY = Infinity
        let maxX = -Infinity
        let maxY = -Infinity

        nodes.forEach(function (node) {
            minX = Math.min(minX, node.x)
            maxX = Math.max(maxX, node.x + node.width)
            minY = Math.min(minY, node.y - node.height / 2)
            maxY = Math.max(maxY, node.y + node.height / 2)
        })

        return {
            nodes: nodes,
            byId: byId,
            edges: source.edges,
            root: rootId,
            bounds: { minX: minX, minY: minY, maxX: maxX, maxY: maxY },
            width: maxX - minX,
            height: maxY - minY
        }
    }

    function displayName(node) {
        return node.surnom ? node.nom + ' ' + node.surnom : node.nom
    }

    function shortName(node, maxLength) {
        const name = displayName(node)
        return name.length > maxLength ? name.slice(0, maxLength - 1) + '…' : name
    }

    function colors(node) {
        let stroke = '#b7ad9d'
        if (node.sexe === 'M') stroke = '#6f96c4'
        if (node.sexe === 'F') stroke = '#bd7d98'
        return {
            fill: node.attention ? '#fff6e4' : '#fffdf9',
            stroke: stroke,
            ink: '#1b2b26',
            muted: '#66746d',
            accent: node.attention ? '#b85c38' : '#3a7d66'
        }
    }

    function clamp(value, minimum, maximum) {
        return Math.max(minimum, Math.min(maximum, value))
    }

    function roundedRect(context, x, y, width, height, radius) {
        const r = Math.min(radius, width / 2, height / 2)
        context.beginPath()
        context.moveTo(x + r, y)
        context.arcTo(x + width, y, x + width, y + height, r)
        context.arcTo(x + width, y + height, x, y + height, r)
        context.arcTo(x, y + height, x, y, r)
        context.arcTo(x, y, x + width, y, r)
        context.closePath()
    }

    function openNode(node) {
        if (node && node.href) window.location.href = node.href
    }

    class SvgRenderer {
        constructor(svg) {
            this.svg = svg
            this.namespace = 'http://www.w3.org/2000/svg'
            this.scale = 1
            this.tx = 0
            this.ty = 0
            this.dragged = false
            this.dragState = null
            this.padding = 54
            this.fullViewBox = {
                x: layout.bounds.minX - this.padding,
                y: layout.bounds.minY - this.padding,
                width: layout.width + this.padding * 2,
                height: layout.height + this.padding * 2
            }
            this.viewBox = Object.assign({}, this.fullViewBox)
            this.build()
            this.bind()
            this.applyTransform()
        }

        build() {
            const svg = this.svg
            svg.setAttribute('viewBox', [
                this.viewBox.x,
                this.viewBox.y,
                this.viewBox.width,
                this.viewBox.height
            ].join(' '))
            svg.setAttribute('preserveAspectRatio', 'xMidYMid meet')
            svg.replaceChildren()

            this.group = document.createElementNS(this.namespace, 'g')
            this.group.setAttribute('class', 'tree-graph-pan')
            svg.appendChild(this.group)

            const edgeGroup = document.createElementNS(this.namespace, 'g')
            edgeGroup.setAttribute('class', 'tree-graph-edges')
            this.group.appendChild(edgeGroup)

            const nodeGroup = document.createElementNS(this.namespace, 'g')
            nodeGroup.setAttribute('class', 'tree-graph-nodes')
            this.group.appendChild(nodeGroup)

            layout.edges.forEach((edge) => {
                const source = layout.byId.get(String(edge.source))
                const target = layout.byId.get(String(edge.target))
                if (!source || !target) return

                const startX = source.centerX
                const startY = source.y + source.height / 2
                const endX = target.centerX
                const endY = target.y - target.height / 2
                const curve = Math.max(30, (endY - startY) * 0.48)
                const path = document.createElementNS(this.namespace, 'path')
                path.setAttribute('d', [
                    'M', startX, startY,
                    'C', startX, startY + curve,
                    endX, endY - curve,
                    endX, endY
                ].join(' '))
                path.setAttribute('class', 'tree-graph-link')
                edgeGroup.appendChild(path)
            })

            layout.nodes.forEach((node) => {
                const palette = colors(node)
                const group = document.createElementNS(this.namespace, 'g')
                group.setAttribute('class', 'tree-graph-node sex-' + String(node.sexe || 'x').toLowerCase())
                group.setAttribute('tabindex', '0')
                group.setAttribute('role', 'link')
                group.setAttribute('aria-label', displayName(node) + ', ' + node.generationLabel + ', ' + node.statusLabel)
                group.dataset.id = String(node.id)

                const title = document.createElementNS(this.namespace, 'title')
                title.textContent = displayName(node) + ' · ' + node.generationLabel + ' · ' + node.statusLabel
                group.appendChild(title)

                const box = document.createElementNS(this.namespace, 'rect')
                box.setAttribute('x', node.x)
                box.setAttribute('y', node.y - node.height / 2)
                box.setAttribute('width', node.width)
                box.setAttribute('height', node.height)
                box.setAttribute('rx', '14')
                box.setAttribute('fill', palette.fill)
                box.setAttribute('stroke', palette.stroke)
                box.setAttribute('class', 'tree-graph-box')
                if (node.attention) box.classList.add('is-attention')
                group.appendChild(box)

                const meta = document.createElementNS(this.namespace, 'text')
                meta.setAttribute('x', node.x + node.width - 11)
                meta.setAttribute('y', node.y - node.height / 2 + 15)
                meta.setAttribute('text-anchor', 'end')
                meta.setAttribute('class', 'tree-graph-meta')
                meta.textContent = node.generationLabel
                group.appendChild(meta)

                const name = document.createElementNS(this.namespace, 'text')
                name.setAttribute('x', node.x + 12)
                name.setAttribute('y', node.y + (node.surnom ? -3 : 5))
                name.setAttribute('class', 'tree-graph-name')
                name.textContent = shortName({ nom: node.nom, surnom: null }, 23)
                group.appendChild(name)

                if (node.surnom) {
                    const surname = document.createElementNS(this.namespace, 'text')
                    surname.setAttribute('x', node.x + 12)
                    surname.setAttribute('y', node.y + 14)
                    surname.setAttribute('class', 'tree-graph-surname')
                    surname.textContent = shortName({ nom: node.surnom, surnom: null }, 26)
                    group.appendChild(surname)
                }

                if (node.attention) {
                    const dot = document.createElementNS(this.namespace, 'circle')
                    dot.setAttribute('cx', node.x + node.width - 12)
                    dot.setAttribute('cy', node.y + node.height / 2 - 10)
                    dot.setAttribute('r', '4')
                    dot.setAttribute('class', 'tree-graph-attention')
                    group.appendChild(dot)
                }

                group.addEventListener('click', (event) => {
                    event.stopPropagation()
                    if (!this.dragged) openNode(node)
                })
                group.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault()
                        openNode(node)
                    }
                })

                nodeGroup.appendChild(group)
            })
        }

        bind() {
            this.svg.addEventListener('pointerdown', (event) => {
                if (event.button !== 0) return
                this.dragged = false
                this.dragState = {
                    pointerId: event.pointerId,
                    x: event.clientX,
                    y: event.clientY,
                    tx: this.tx,
                    ty: this.ty
                }
                this.svg.setPointerCapture(event.pointerId)
                this.svg.classList.add('is-dragging')
            })

            this.svg.addEventListener('pointermove', (event) => {
                if (!this.dragState) return
                const rect = this.svg.getBoundingClientRect()
                const factorX = this.viewBox.width / Math.max(rect.width, 1)
                const factorY = this.viewBox.height / Math.max(rect.height, 1)
                const dx = (event.clientX - this.dragState.x) * factorX
                const dy = (event.clientY - this.dragState.y) * factorY
                if (Math.abs(dx) > 2 || Math.abs(dy) > 2) this.dragged = true
                this.tx = this.dragState.tx + dx
                this.ty = this.dragState.ty + dy
                this.applyTransform()
            })

            const stopDrag = () => {
                this.dragState = null
                this.svg.classList.remove('is-dragging')
            }
            this.svg.addEventListener('pointerup', stopDrag)
            this.svg.addEventListener('pointercancel', stopDrag)

            this.svg.addEventListener('wheel', (event) => {
                event.preventDefault()
                const point = this.toViewBox(event.clientX, event.clientY)
                this.zoomAt(point.x, point.y, Math.exp(-event.deltaY * 0.0015))
            }, { passive: false })
        }

        toViewBox(clientX, clientY) {
            const point = this.svg.createSVGPoint()
            point.x = clientX
            point.y = clientY
            return point.matrixTransform(this.svg.getScreenCTM().inverse())
        }

        applyTransform() {
            this.group.setAttribute('transform', 'translate(' + this.tx + ' ' + this.ty + ') scale(' + this.scale + ')')
            const matrix = this.group.getScreenCTM()
            const effectiveScale = matrix ? Math.abs(matrix.a) : this.scale
            const zoomedOut = effectiveScale < 0.6
            this.svg.classList.toggle('is-zoomed-out', zoomedOut)
            if (this.svg.parentElement) this.svg.parentElement.classList.toggle('is-zoomed-out', zoomedOut)
        }

        zoomAt(x, y, factor) {
            const nextScale = clamp(this.scale * factor, 0.35, 24)
            const ratio = nextScale / this.scale
            this.tx = x - (x - this.tx) * ratio
            this.ty = y - (y - this.ty) * ratio
            this.scale = nextScale
            this.applyTransform()
        }

        zoom(factor) {
            const rect = this.svg.getBoundingClientRect()
            const point = this.toViewBox(rect.left + rect.width / 2, rect.top + rect.height / 2)
            this.zoomAt(point.x, point.y, factor)
        }

        setViewBox(box) {
            this.viewBox = box
            this.svg.setAttribute('viewBox', [box.x, box.y, box.width, box.height].join(' '))
        }

        focus() {
            const rect = this.svg.getBoundingClientRect()
            const root = layout.byId.get(String(layout.root)) || layout.nodes[0]
            const aspect = rect.width > 0 && rect.height > 0 ? rect.width / rect.height : 2
            let width = clamp((rect.width || this.fullViewBox.width) / 0.8, 720, this.fullViewBox.width)
            let height = width / Math.max(aspect, 0.6)

            if (height > this.fullViewBox.height) {
                height = this.fullViewBox.height
                width = height * Math.max(aspect, 0.6)
            }

            const x = clamp(
                root.centerX - width / 2,
                this.fullViewBox.x,
                this.fullViewBox.x + this.fullViewBox.width - width
            )
            const y = clamp(
                root.y - 64,
                this.fullViewBox.y,
                this.fullViewBox.y + this.fullViewBox.height - height
            )

            this.scale = 1
            this.tx = 0
            this.ty = 0
            this.setViewBox({ x: x, y: y, width: width, height: height })
            this.applyTransform()
        }

        reset() {
            this.scale = 1
            this.tx = 0
            this.ty = 0
            this.setViewBox(Object.assign({}, this.fullViewBox))
            this.applyTransform()
        }

        activate() {
            this.focus()
            window.requestAnimationFrame(() => this.applyTransform())
        }

        deactivate() {}
    }

    class ThreeRenderer {
        constructor(canvas, container, tooltip) {
            this.canvas = canvas
            this.context = canvas.getContext('2d')
            this.container = container
            this.tooltip = tooltip
            this.width = 0
            this.height = 0
            this.ratio = 1
            this.zoom = 1
            this.centerX = 0
            this.centerY = 0
            this.yaw = -0.4
            this.pitch = 0.18
            this.dragged = false
            this.dragState = null
            this.hoverId = null
            this.frame = null
            this.running = false
            this.hasFitted = false
            this.autoRotate = false
            this.onAutoRotateChange = syncRotateButton
            this.defaultYaw = this.yaw
            this.defaultPitch = this.pitch
            this.fontFamily = getComputedStyle(document.body).fontFamily
            this.nodes = this.buildNodes()
            this.bind()
            this.observer = new ResizeObserver(() => this.resize())
            this.observer.observe(container)
        }

        buildNodes() {
            const centerWorldX = (layout.bounds.minX + layout.bounds.maxX) / 2
            const centerWorldY = (layout.bounds.minY + layout.bounds.maxY) / 2
            const halfWidth = Math.max(layout.width / 2, 1)
            const ringStep = 120

            return layout.nodes.map(function (node) {
                const normalizedX = clamp((node.centerX - centerWorldX) / halfWidth, -1, 1)
                const angle = normalizedX * 1.8
                const radius = node.depth * ringStep

                return Object.assign({}, node, {
                    x3: Math.sin(angle) * radius,
                    y3: node.y - centerWorldY,
                    z3: -Math.cos(angle) * radius
                })
            })
        }

        resize() {
            const rect = this.container.getBoundingClientRect()
            if (rect.width < 10 || rect.height < 10) return
            const previousWidth = this.width
            const previousHeight = this.height
            this.ratio = window.devicePixelRatio || 1
            this.width = rect.width
            this.height = rect.height
            this.canvas.width = Math.round(rect.width * this.ratio)
            this.canvas.height = Math.round(rect.height * this.ratio)
            this.canvas.style.width = rect.width + 'px'
            this.canvas.style.height = rect.height + 'px'

            if (!this.hasFitted) {
                this.fit()
                this.hasFitted = true
            } else {
                this.centerX += (this.width - previousWidth) / 2
                this.centerY += (this.height - previousHeight) / 2
            }

            if (this.running) this.draw()
        }

        project(node) {
            const cosYaw = Math.cos(this.yaw)
            const sinYaw = Math.sin(this.yaw)
            const cosPitch = Math.cos(this.pitch)
            const sinPitch = Math.sin(this.pitch)

            const x1 = node.x3 * cosYaw + node.z3 * sinYaw
            const z1 = -node.x3 * sinYaw + node.z3 * cosYaw
            const y1 = node.y3 * cosPitch - z1 * sinPitch
            const z2 = node.y3 * sinPitch + z1 * cosPitch
            const perspective = 2600 / (2600 + z2)
            const scale = perspective * this.zoom

            return {
                x: this.centerX + x1 * scale,
                y: this.centerY + y1 * scale,
                z: z2,
                scale: scale
            }
        }

        fit() {
            const padding = 54
            const projected = this.nodes.map((node) => {
                const savedZoom = this.zoom
                const savedCenterX = this.centerX
                const savedCenterY = this.centerY
                this.zoom = 1
                this.centerX = 0
                this.centerY = 0
                const point = this.project(node)
                this.zoom = savedZoom
                this.centerX = savedCenterX
                this.centerY = savedCenterY
                return point
            })

            let minX = Infinity
            let minY = Infinity
            let maxX = -Infinity
            let maxY = -Infinity
            projected.forEach(function (point) {
                minX = Math.min(minX, point.x)
                minY = Math.min(minY, point.y)
                maxX = Math.max(maxX, point.x)
                maxY = Math.max(maxY, point.y)
            })

            const width = Math.max(maxX - minX, 1)
            const height = Math.max(maxY - minY, 1)
            this.zoom = clamp(
                Math.min((this.width - padding * 2) / width, (this.height - padding * 2) / height),
                0.02,
                2
            )
            this.centerX = this.width / 2 - ((minX + maxX) / 2) * this.zoom
            this.centerY = this.height / 2 - ((minY + maxY) / 2) * this.zoom
        }

        draw() {
            if (!this.width || !this.height) return
            const context = this.context
            context.setTransform(this.ratio, 0, 0, this.ratio, 0, 0)
            context.clearRect(0, 0, this.width, this.height)

            const projected = new Map()
            let minDepth = Infinity
            let maxDepth = -Infinity

            this.nodes.forEach((node) => {
                const point = this.project(node)
                projected.set(String(node.id), point)
                minDepth = Math.min(minDepth, point.z)
                maxDepth = Math.max(maxDepth, point.z)
            })

            const depthSpan = Math.max(maxDepth - minDepth, 1)
            const depthRatio = (depth) => clamp((depth - minDepth) / depthSpan, 0, 1)

            const edgeList = []
            layout.edges.forEach((edge) => {
                const source = projected.get(String(edge.source))
                const target = projected.get(String(edge.target))
                if (!source || !target) return
                edgeList.push({
                    source: source,
                    target: target,
                    depth: (source.z + target.z) / 2
                })
            })
            edgeList.sort(function (a, b) { return b.depth - a.depth })

            edgeList.forEach((edge) => {
                const near = 1 - depthRatio(edge.depth)
                context.strokeStyle = 'rgba(58, 125, 102, ' + (0.1 + near * 0.5) + ')'
                context.lineWidth = 0.55 + near * 1.45
                context.beginPath()
                context.moveTo(edge.source.x, edge.source.y)
                context.lineTo(edge.target.x, edge.target.y)
                context.stroke()
            })

            const sorted = this.nodes.slice().sort(function (a, b) {
                return projected.get(String(b.id)).z - projected.get(String(a.id)).z
            })

            sorted.forEach((node) => {
                const point = projected.get(String(node.id))
                const palette = colors(node)
                const near = 1 - depthRatio(point.z)
                const radius = clamp(3.2 + point.scale * 6.8, 2.8, 11)
                const hovered = this.hoverId === String(node.id)

                context.globalAlpha = 0.45 + near * 0.55

                if (String(node.id) === String(layout.root)) {
                    context.beginPath()
                    context.arc(point.x, point.y, radius + 6, 0, Math.PI * 2)
                    context.fillStyle = 'rgba(201, 154, 61, .15)'
                    context.fill()
                }

                context.beginPath()
                context.arc(point.x, point.y, hovered ? radius + 3 : radius, 0, Math.PI * 2)
                context.fillStyle = palette.fill
                context.fill()
                context.lineWidth = hovered ? 2.8 : 1.5
                context.strokeStyle = hovered ? '#1f4d3e' : palette.stroke
                context.stroke()

                if (node.attention) {
                    context.beginPath()
                    context.arc(
                        point.x + radius * 0.78,
                        point.y - radius * 0.78,
                        Math.max(2.1, radius * 0.28),
                        0,
                        Math.PI * 2
                    )
                    context.fillStyle = palette.accent
                    context.fill()
                }
            })

            context.globalAlpha = 1

            const placed = []
            const labelOrder = this.nodes.slice().sort(function (a, b) {
                return projected.get(String(a.id)).z - projected.get(String(b.id)).z
            })

            labelOrder.forEach((node) => {
                if (this.hoverId === String(node.id)) return
                const point = projected.get(String(node.id))
                const palette = colors(node)
                this.drawLabel(context, node, point, palette, false, placed)
            })

            if (this.hoverId !== null) {
                const hoveredNode = layout.byId.get(String(this.hoverId))
                const hoveredPoint = projected.get(String(this.hoverId))
                if (hoveredNode && hoveredPoint) {
                    this.drawLabel(context, hoveredNode, hoveredPoint, colors(hoveredNode), true, placed)
                }
            }
        }

        drawLabel(context, node, point, palette, hovered, placed) {
            const text = shortName(node, hovered ? 38 : 22)
            const fontSize = hovered ? 12 : 11
            const radius = clamp(3.2 + point.scale * 6.8, 2.8, 11)
            context.font = (hovered ? '700 ' : '600 ') + fontSize + 'px ' + this.fontFamily
            context.textAlign = 'left'
            context.textBaseline = 'middle'

            const textWidth = context.measureText(text).width
            const boxWidth = textWidth + (hovered ? 14 : 0)
            const boxHeight = hovered ? 22 : 16
            const gap = hovered ? 9 : 6

            const candidates = [
                { x: point.x + radius + gap, y: point.y - boxHeight / 2 },
                { x: point.x - radius - gap - boxWidth, y: point.y - boxHeight / 2 },
                { x: point.x - boxWidth / 2, y: point.y + radius + gap },
                { x: point.x - boxWidth / 2, y: point.y - radius - gap - boxHeight }
            ]

            let chosen = null
            for (let index = 0; index < candidates.length; index += 1) {
                const candidate = candidates[index]
                const rect = {
                    x: candidate.x - 3,
                    y: candidate.y - 2,
                    width: boxWidth + 6,
                    height: boxHeight + 4
                }
                if (rect.x < 6 || rect.y < 6 || rect.x + rect.width > this.width - 6 || rect.y + rect.height > this.height - 6) {
                    continue
                }
                const overlaps = placed.some(function (other) {
                    return !(
                        rect.x + rect.width < other.x
                        || other.x + other.width < rect.x
                        || rect.y + rect.height < other.y
                        || other.y + other.height < rect.y
                    )
                })
                if (!overlaps) {
                    chosen = { candidate: candidate, rect: rect }
                    break
                }
            }

            if (!chosen && hovered) {
                const candidate = candidates[0]
                chosen = {
                    candidate: candidate,
                    rect: { x: candidate.x - 3, y: candidate.y - 2, width: boxWidth + 6, height: boxHeight + 4 }
                }
            }

            if (!chosen) return
            placed.push(chosen.rect)

            if (hovered) {
                roundedRect(context, chosen.rect.x, chosen.rect.y, chosen.rect.width, chosen.rect.height, 7)
                context.fillStyle = 'rgba(255, 253, 249, .98)'
                context.fill()
                context.lineWidth = 1
                context.strokeStyle = 'rgba(207, 198, 182, .9)'
                context.stroke()
                context.fillStyle = palette.ink
                context.fillText(text, chosen.candidate.x + 7, chosen.candidate.y + boxHeight / 2)
                return
            }

            context.lineWidth = 3
            context.lineJoin = 'round'
            context.strokeStyle = 'rgba(255, 253, 249, .92)'
            context.strokeText(text, chosen.candidate.x, chosen.candidate.y + boxHeight / 2)
            context.fillStyle = palette.ink
            context.fillText(text, chosen.candidate.x, chosen.candidate.y + boxHeight / 2)
        }

        nearest(clientX, clientY) {
            const rect = this.canvas.getBoundingClientRect()
            const x = clientX - rect.left
            const y = clientY - rect.top
            let best = null
            let bestDistance = 18

            this.nodes.forEach((node) => {
                const point = this.project(node)
                const distance = Math.hypot(point.x - x, point.y - y)
                if (distance < bestDistance) {
                    bestDistance = distance
                    best = node
                }
            })

            return best
        }

        updateTooltip(node, clientX, clientY) {
            if (!node) {
                this.tooltip.hidden = true
                return
            }

            const rect = this.container.getBoundingClientRect()
            this.tooltip.textContent = displayName(node) + ' · ' + node.generationLabel + ' · ' + node.statusLabel
            this.tooltip.hidden = false

            const maxLeft = Math.max(8, rect.width - this.tooltip.offsetWidth - 8)
            const maxTop = Math.max(8, rect.height - this.tooltip.offsetHeight - 8)
            this.tooltip.style.left = clamp(clientX - rect.left + 14, 8, maxLeft) + 'px'
            this.tooltip.style.top = clamp(clientY - rect.top + 14, 8, maxTop) + 'px'
        }

        bind() {
            this.canvas.addEventListener('pointerdown', (event) => {
                if (event.button !== 0) return
                this.dragged = false
                this.setAutoRotate(false)
                this.dragState = {
                    pointerId: event.pointerId,
                    x: event.clientX,
                    y: event.clientY,
                    yaw: this.yaw,
                    pitch: this.pitch
                }
                this.canvas.setPointerCapture(event.pointerId)
                this.canvas.classList.add('is-dragging')
            })

            this.canvas.addEventListener('pointermove', (event) => {
                if (this.dragState) {
                    const dx = event.clientX - this.dragState.x
                    const dy = event.clientY - this.dragState.y
                    if (Math.abs(dx) > 3 || Math.abs(dy) > 3) this.dragged = true
                    this.yaw = this.dragState.yaw + dx * 0.007
                    this.pitch = clamp(this.dragState.pitch + dy * 0.005, -1.1, 1.1)
                    this.draw()
                    return
                }

                const node = this.nearest(event.clientX, event.clientY)
                this.hoverId = node ? String(node.id) : null
                this.canvas.style.cursor = node ? 'pointer' : 'grab'
                this.updateTooltip(node, event.clientX, event.clientY)
                this.draw()
            })

            const stopDrag = (event) => {
                const hadMoved = this.dragged
                this.dragState = null
                this.canvas.classList.remove('is-dragging')
                if (!hadMoved && event.type === 'pointerup') {
                    const node = this.nearest(event.clientX, event.clientY)
                    if (node) openNode(node)
                }
                this.draw()
            }
            this.canvas.addEventListener('pointerup', stopDrag)
            this.canvas.addEventListener('pointercancel', stopDrag)
            this.canvas.addEventListener('pointerleave', () => {
                this.hoverId = null
                this.updateTooltip(null)
                this.draw()
            })

            this.canvas.addEventListener('wheel', (event) => {
                event.preventDefault()
                this.setAutoRotate(false)
                this.zoom = clamp(this.zoom * Math.exp(-event.deltaY * 0.0014), 0.02, 8)
                this.draw()
            }, { passive: false })
        }

        startAnimation() {
            if (!this.running || !this.autoRotate || this.frame !== null) return

            const loop = () => {
                if (!this.running || !this.autoRotate) {
                    this.frame = null
                    return
                }
                this.yaw += 0.0022
                this.draw()
                this.frame = window.requestAnimationFrame(loop)
            }

            this.frame = window.requestAnimationFrame(loop)
        }

        stopAnimation() {
            if (this.frame !== null) window.cancelAnimationFrame(this.frame)
            this.frame = null
        }

        setAutoRotate(value) {
            const next = Boolean(value) && !reduceMotion.matches
            this.autoRotate = next

            if (next) {
                this.startAnimation()
            } else {
                this.stopAnimation()
            }

            if (typeof this.onAutoRotateChange === 'function') this.onAutoRotateChange()
        }

        toggleAutoRotate() {
            if (reduceMotion.matches) return
            this.setAutoRotate(!this.autoRotate)
        }

        zoomIn(factor) {
            this.setAutoRotate(false)
            this.zoom = clamp(this.zoom * factor, 0.02, 8)
            this.draw()
        }

        zoom(factor) {
            this.zoomIn(factor)
        }

        reset() {
            this.setAutoRotate(false)
            this.yaw = this.defaultYaw
            this.pitch = this.defaultPitch
            this.centerX = 0
            this.centerY = 0
            this.fit()
            this.hasFitted = true
            this.draw()
        }

        activate() {
            this.running = true
            this.resize()
            if (!this.hasFitted) {
                this.fit()
                this.hasFitted = true
            }
            this.draw()
            if (this.autoRotate) this.startAnimation()
        }

        deactivate() {
            this.running = false
            this.stopAnimation()
            this.updateTooltip(null)
        }
    }

    function rendererFor(view) {
        if (renderers[view]) return renderers[view]

        try {
            if (view === '2d') {
                const svg = document.querySelector('[data-tree-graph="2d"] svg')
                renderers[view] = new SvgRenderer(svg)
            } else if (view === '3d') {
                const container = document.querySelector('[data-tree-graph="3d"]')
                const canvas = container.querySelector('canvas')
                const tooltip = container.querySelector('[data-tree-tooltip]')
                renderers[view] = new ThreeRenderer(canvas, container, tooltip)
            }
        } catch (error) {
            console.warn('Vue indisponible:', view, error)
            return null
        }

        return renderers[view]
    }

    function syncRotateButton() {
        if (!rotateButton) return
        const active = activeView === '3d' && activeRenderer !== null && activeRenderer.autoRotate === true
        rotateButton.hidden = activeView !== '3d'
        rotateButton.textContent = active ? 'Pause rotation' : 'Rotation auto'
        rotateButton.setAttribute('aria-pressed', active ? 'true' : 'false')
    }

    function setView(view, updateUrl) {
        if (view === 'canvas') view = '2d'
        if (!['standard', '2d', '3d'].includes(view)) view = 'standard'

        if (activeRenderer) {
            activeRenderer.deactivate()
            activeRenderer = null
        }

        panels.forEach(function (panel) {
            const active = panel.dataset.treeViewPanel === view
            panel.hidden = !active
            panel.classList.toggle('is-active', active)
        })

        switchButtons.forEach(function (button) {
            const active = button.dataset.treeViewButton === view
            button.classList.toggle('is-active', active)
            button.setAttribute('aria-pressed', active ? 'true' : 'false')
        })

        const graphical = view !== 'standard'
        if (graphToolbar) graphToolbar.hidden = !graphical
        if (graphHint) graphHint.hidden = !graphical

        if (graphical) {
            activeRenderer = rendererFor(view)
            if (!activeRenderer) {
                setView('standard', updateUrl)
                return
            }
            activeRenderer.activate()
            if (graphCaption) {
                const label = view === '2d' ? 'Vue 2D' : 'Vue 3D'
                graphCaption.textContent = label + ' · ' + layout.nodes.length + ' personnes'
            }
            if (graphHint) {
                graphHint.textContent = view === '3d'
                    ? 'Glissez pour tourner · molette pour zoomer · cliquez sur une fiche.'
                    : 'Cliquez sur une fiche pour l’ouvrir. Molette : zoom · glisser : déplacer.'
            }
        }

        activeView = view
        syncRotateButton()

        if (updateUrl) {
            try {
                const url = new URL(window.location.href)
                if (view === 'standard') {
                    url.searchParams.delete('view')
                } else {
                    url.searchParams.set('view', view)
                }
                window.history.replaceState(null, '', url)
            } catch (error) {
                // Les URL file:// de l’export statique peuvent refuser l’historique.
            }
        }
    }

    switchButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            setView(button.dataset.treeViewButton, true)
        })
    })

    if (rotateButton) {
        rotateButton.addEventListener('click', function () {
            if (!activeRenderer || typeof activeRenderer.toggleAutoRotate !== 'function') return
            activeRenderer.toggleAutoRotate()
        })
    }

    zoomButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!activeRenderer) return
            if (button.dataset.treeZoom === 'reset') {
                activeRenderer.reset()
            } else {
                activeRenderer.zoom(button.dataset.treeZoom === 'in' ? 1.25 : 0.8)
            }
        })
    })

    window.addEventListener('popstate', function () {
        const view = new URL(window.location.href).searchParams.get('view') || 'standard'
        setView(view, false)
    })

    const initialView = new URL(window.location.href).searchParams.get('view') || 'standard'
    setView(initialView, false)
})()
