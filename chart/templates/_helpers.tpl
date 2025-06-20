{{- define "website-v4.fullname" -}}
{{- printf "%s-website-v4" .Values.environment }}
{{- end }}

{{- define "website-v4.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{- define "website-v4.labels" -}}
helm.sh/chart: {{ include "website-v4.chart" . }}
app.kubernetes.io/name: {{ include "website-v4.fullname" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{ include "website-v4.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}


{{- define "website-v4.selectorLabels" -}}
backstage.uk/environment: {{ .Values.environment }}
backstage.uk/component: website-v4
{{- end }}

{{- define "website-v4.ingress-middleware" -}}
{{- $middlewares := list "default-redirect-http-to-https@kubernetescrd" }}
{{- if not (eq "prod" .Values.environment) }}{{ $middlewares = append $middlewares "backstage-basic-auth@kubernetescrd" }}{{ end }}
{{- join "," $middlewares }}
{{- end }}
