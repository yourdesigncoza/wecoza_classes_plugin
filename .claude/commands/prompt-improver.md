---

description: Improve prompt quality using project context.
globs:
------

Priority: High
Instructions: MUST follow \<prompt\_rewriting\_rules> for rewriting the Ultrathink and Improve this prompt: $ARGUMENTS

```xml
<?xml version="1.0" encoding="UTF-8"?>
<prompt_rewriting_rules version="1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <metadata>
    <author>LLM Architecture Team</author>
    <created>2025-03-25</created>
    <purpose>Query analysis and improvement</purpose>
    <application-boundary>
      <limit>Applies to user query analysis and rewriting</limit>
      <limit>Does not modify conversational response generation</limit>
      <limit>Preserves original user intent</limit>
    </application-boundary>
  </metadata>
  
  <objective priority="critical">
    <definition>Analyze and improve user queries while preserving intent</definition>
    <requirements>
      <requirement type="preservation">Maintain original user goals</requirement>
      <requirement type="analysis">Identify improvement opportunities</requirement>
      <requirement type="generation">Propose effective rewrites</requirement>
    </requirements>
  </objective>
  
  <analysis_process priority="high">
    <phase name="InputEvaluation" sequence="1">
      <step name="QueryExamination">
        <action>Analyze user query for clarity, specificity, and completeness</action>
        <evaluation_criteria>
          <criterion type="clarity">Clear communication of needs</criterion>
          <criterion type="specificity">Sufficient detail level</criterion>
          <criterion type="context">Proper utilization of conversation history</criterion>
        </evaluation_criteria>
      </step>

      <!-- New step added for code‑aware context retrieval -->
      <step name="CodebaseRetrieval">
        <action>Identify identifiers (functions, classes, file paths) mentioned in the Query; fetch matching
                snippets from the repository; attach them to CodeContext.</action>
        <evaluation_criteria>
          <criterion type="precision">Snippets must correspond to referenced identifiers.</criterion>
          <criterion type="brevity">Avoid including complete files when excerpts suffice.</criterion>
        </evaluation_criteria>
      </step>

      <step name="ModificationDetermination">
        <action>Decide if query requires improvement</action>
        <output format="boolean">YES or NO response</output>
      </step>
    </phase>
    
    <phase name="ImprovementIdentification" sequence="2" conditional="ModificationDetermination=YES">
      <step name="AspectIdentification">
        <action>List specific aspects requiring improvement</action>
        <aspects>
          <aspect type="clarity">Communication clarity</aspect>
          <aspect type="specificity">Detail sufficiency</aspect>
          <aspect type="structure">Query organization</aspect>
          <aspect type="relevance">Contextual alignment</aspect>
        </aspects>
      </step>
    </phase>
    
    <phase name="EffectivenessIdentification" sequence="2" conditional="ModificationDetermination=NO">
      <step name="StrengthIdentification">
        <action>Identify effective aspects of the query</action>
        <strengths>
          <strength type="clarity">Clear communication</strength>
          <strength type="specificity">Appropriate detail</strength>
          <strength type="structure">Logical organization</strength>
          <strength type="relevance">Contextual relevance</strength>
        </strengths>
      </step>
    </phase>
  </analysis_process>
  
  <rewriting_protocol priority="critical">
    <generation_rules>
      <rule name="IntentPreservation">
        <definition>Maintain user's original goal and intent</definition>
        <implementation>
          <strategy>Compare original query purpose with rewrite</strategy>
          <strategy>Preserve key question elements</strategy>
          <strategy>Maintain task-specific instructions</strategy>
        </implementation>
      </rule>
      
      <rule name="ContextIntegration">
        <definition>Incorporate relevant conversational history</definition>
        <implementation>
          <strategy>Reference established concepts from prior exchanges</strategy>
          <strategy>Avoid redundant information already provided</strategy>
          <strategy>Discard irrelevant historical context</strategy>
          <!-- new strategy for code context -->
          <strategy>Integrate CodeContext snippets when they clarify API contracts, function behaviour, or module usage.</strategy>
        </implementation>
        <validation>
          <check type="relevance">Context must relate to current query topic</check>
          <check type="recency">Prioritize recent conversational turns</check>
          <!-- new validation -->
          <check type="code_relevance">Each included snippet must be referenced in Query or rewrite.</check>
        </validation>
      </rule>
      
      <rule name="ClarityEnhancement">
        <definition>Improve communication clarity</definition>
        <implementation>
          <strategy>Restructure ambiguous phrasing</strategy>
          <strategy>Add explicit structure when beneficial</strategy>
          <strategy>Remove unnecessary verbosity</strategy>
        </implementation>
      </rule>
    </generation_rules>
    
    <rewrite_ordering>
      <criterion priority="1">Likelihood of matching user intent</criterion>
      <criterion priority="2">Minimal assumption introduction</criterion>
      <criterion priority="3">Clarity improvement magnitude</criterion>
    </rewrite_ordering>
  </rewriting_protocol>
  
  <assumption_management priority="high">
    <evaluation_required>YES or NO determination</evaluation_required>
    <assumption_attributes>
      <attribute name="salience" values="HIGH,MID,LOW">
        <definition>Importance to query effectiveness</definition>
      </attribute>
      <attribute name="plausibility" values="HIGH,MID,LOW">
        <definition>Likelihood of user agreement</definition>
      </attribute>
    </assumption_attributes>
    <documentation_format>
      <table columns="assumption,salience,plausibility"/>
    </documentation_format>
  </assumption_management>
  
  <output_template priority="critical">
    <section name="ModificationRequired">
      <question>Does the Query need modification?</question>
      <format>YES or NO response</format>
    </section>
    
    <section name="AnalysisReasoning" conditional="true">
      <condition applies-to="ModificationRequired=YES">
        <content>Specific aspects requiring improvement</content>
      </condition>
      <condition applies-to="ModificationRequired=NO">
        <content>Effective aspects of the query</content>
      </condition>
    </section>
    
    <section name="ProposedRewrites">
      <format>Numbered list of rewrites</format>
      <ordering>Most to least likely effective</ordering>
    </section>
    
    <section name="AssumptionsRequired">
      <question>Does the rewrite require assumptions not present in Query or Conversational History?</question>
      <format>YES or NO response</format>
    </section>
    
    <section name="Assumptions" conditional="AssumptionsRequired=YES">
      <format>Markdown table with columns: assumption, salience, plausibility</format>
      <values for="salience">HIGH, MID, LOW</values>
      <values for="plausibility">HIGH, MID, LOW</values>
    </section>

    <!-- new section to expose injected code context -->
    <section name="IncludedCodeContext" conditional="CodeContext">
      <format>Fenced code blocks or bullet list of file↔snippet pairs</format>
    </section>
  </output_template>
  
  <input_structure>
    <component name="ConversationalHistory" required="false">
      <description>Previous exchanges providing context</description>
      <processing>
        <instruction>Use if relevant to current query topic</instruction>
        <instruction>Discard if about different task or topic</instruction>
      </processing>
    </component>
    
    <!-- New optional component for code snippets -->
    <component name="CodeContext" required="false">
      <description>Code snippets relevant to the query.</description>
      <processing>
        <instruction>Select only fragments that clarify requested functions, APIs, or modules.</instruction>
        <instruction>Summarise or truncate snippets to ≤150 tokens each.</instruction>
      </processing>
    </component>
    
    <component name="Query" required="true">
      <description>Current user question or instruction</description>
      <processing>
        <instruction>Analyze for improvement opportunities</instruction>
        <instruction>Preserve original intent when rewriting</instruction>
      </processing>
    </component>
  </input_structure>
  
  <compliance_validation>
    <!-- updated for six sections -->
    <validation xpath="count(//section) = 6" message="All output sections must be present"/>
    <validation xpath="every $v in //values/@for satisfies $v = 'salience' or $v = 'plausibility'"
               message="Value attributes must be either salience or plausibility"/>
    <validation xpath="count(//rule) >= 3" message="At least three rewriting rules required"/>
  </compliance_validation>
</prompt_rewriting_rules>
```
